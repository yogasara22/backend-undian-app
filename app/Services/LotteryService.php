<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Prize;
use App\Models\Winner;
use App\Models\ScheduledWinner;
use Illuminate\Support\Facades\DB;

class LotteryService
{
    public function draw(?int $prizeId = null): array
    {
        return DB::transaction(function () use ($prizeId) {

            // 1. Cek antrian scheduled winners (FIFO berdasarkan priority & created_at)
            $scheduledQuery = ScheduledWinner::orderBy('priority')
                ->orderBy('created_at')
                ->lockForUpdate();
            
            // Jika ada specific prizeId, cari antrian yang sesuai hadiah tersebut
            if ($prizeId) {
                $scheduledQuery->where('prize_id', $prizeId);
            }

            $scheduled = $scheduledQuery->first();

            if ($scheduled) {
                // Ambil peserta berdasarkan NIK dari scheduled winner
                $participant = Participant::where('nik', $scheduled->nik)
                    ->lockForUpdate()
                    ->first();
                
                if (! $participant) {
                    throw new \Exception(
                        "Peserta dengan NIK {$scheduled->nik} (pemenang terjadwal) tidak ditemukan di database."
                    );
                }

                if ($participant->winners()->exists()) {
                    // Hapus dari antrian jika sudah pernah menang (mencegah loop/error terus menerus)
                    $scheduled->delete();
                    throw new \Exception(
                        "Peserta {$participant->name} ({$scheduled->nik}) sudah pernah menang sebelumnya. Antrian manipulasi dihapus."
                    );
                }

                $prize = Prize::lockForUpdate()->find($scheduled->prize_id);

                if (! $prize) {
                    throw new \Exception('Hadiah untuk pemenang terjadwal tidak ditemukan.');
                }

                // Hapus scheduled winner agar tidak muncul lagi di antrian manipulasi
                $scheduled->delete();

            } else {
                // 2. Undian acak: pilih peserta yang BELUM PERNAH menang
                $participant = Participant::whereDoesntHave('winners')
                    ->lockForUpdate()
                    ->inRandomOrder()
                    ->first();

                if (! $participant) {
                    throw new \Exception('Semua peserta sudah pernah menang. Tidak ada peserta tersisa.');
                }

                // 3. Pilih hadiah
                if ($prizeId) {
                    // Cek ketersediaan stok hadiah yang di-request
                    $prize = Prize::where('id', $prizeId)
                        ->whereRaw('qty > (SELECT COUNT(*) FROM winners WHERE winners.prize_id = prizes.id)')
                        ->lockForUpdate()
                        ->first();

                    if (! $prize) {
                        throw new \Exception('Hadiah yang dipilih sudah habis atau tidak ditemukan.');
                    }
                } else {
                    // Default behavior (acak dari yang tersedia)
                    $prize = Prize::whereRaw('qty > (SELECT COUNT(*) FROM winners WHERE winners.prize_id = prizes.id)')
                        ->lockForUpdate()
                        ->inRandomOrder()
                        ->first();

                    if (! $prize) {
                        throw new \Exception('Tidak ada hadiah yang tersisa untuk diundi.');
                    }
                }
            }

            // 4. Validasi stok hadiah (Safety check)
            if ($prize->remaining_qty <= 0) {
                throw new \Exception("Stok hadiah '{$prize->name}' sudah habis.");
            }

            // 5. Catat pemenang

            $winner = Winner::create([
                'participant_id' => $participant->id,
                'prize_id'       => $prize->id,
                'drawn_at'       => now(),
            ]);

            return compact('participant', 'prize', 'winner');
        });
    }
}