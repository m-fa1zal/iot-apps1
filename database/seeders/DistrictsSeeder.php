<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = [
            // Johor (state_id: 1)
            ['state_code' => 'JHR', 'name' => 'Johor Bahru', 'district_code' => 'JHR01'],
            ['state_code' => 'JHR', 'name' => 'Batu Pahat', 'district_code' => 'JHR02'],
            ['state_code' => 'JHR', 'name' => 'Muar', 'district_code' => 'JHR03'],
            ['state_code' => 'JHR', 'name' => 'Pontian', 'district_code' => 'JHR04'],
            ['state_code' => 'JHR', 'name' => 'Kulai', 'district_code' => 'JHR05'],
            ['state_code' => 'JHR', 'name' => 'Kluang', 'district_code' => 'JHR06'],
            ['state_code' => 'JHR', 'name' => 'Segamat', 'district_code' => 'JHR07'],
            ['state_code' => 'JHR', 'name' => 'Mersing', 'district_code' => 'JHR08'],
            ['state_code' => 'JHR', 'name' => 'Kota Tinggi', 'district_code' => 'JHR09'],
            ['state_code' => 'JHR', 'name' => 'Tangkak', 'district_code' => 'JHR10'],

            // Kedah (state_id: 2)
            ['state_code' => 'KDH', 'name' => 'Alor Setar', 'district_code' => 'KDH01'],
            ['state_code' => 'KDH', 'name' => 'Sungai Petani', 'district_code' => 'KDH02'],
            ['state_code' => 'KDH', 'name' => 'Kulim', 'district_code' => 'KDH03'],
            ['state_code' => 'KDH', 'name' => 'Langkawi', 'district_code' => 'KDH04'],
            ['state_code' => 'KDH', 'name' => 'Kuala Kedah', 'district_code' => 'KDH05'],
            ['state_code' => 'KDH', 'name' => 'Pendang', 'district_code' => 'KDH06'],
            ['state_code' => 'KDH', 'name' => 'Padang Terap', 'district_code' => 'KDH07'],
            ['state_code' => 'KDH', 'name' => 'Pokok Sena', 'district_code' => 'KDH08'],
            ['state_code' => 'KDH', 'name' => 'Kubang Pasu', 'district_code' => 'KDH09'],
            ['state_code' => 'KDH', 'name' => 'Kota Setar', 'district_code' => 'KDH10'],
            ['state_code' => 'KDH', 'name' => 'Bandar Baharu', 'district_code' => 'KDH11'],
            ['state_code' => 'KDH', 'name' => 'Baling', 'district_code' => 'KDH12'],

            // Kelantan (state_id: 3)
            ['state_code' => 'KTN', 'name' => 'Kota Bharu', 'district_code' => 'KTN01'],
            ['state_code' => 'KTN', 'name' => 'Tumpat', 'district_code' => 'KTN02'],
            ['state_code' => 'KTN', 'name' => 'Pasir Mas', 'district_code' => 'KTN03'],
            ['state_code' => 'KTN', 'name' => 'Tanah Merah', 'district_code' => 'KTN04'],
            ['state_code' => 'KTN', 'name' => 'Machang', 'district_code' => 'KTN05'],
            ['state_code' => 'KTN', 'name' => 'Kuala Krai', 'district_code' => 'KTN06'],
            ['state_code' => 'KTN', 'name' => 'Gua Musang', 'district_code' => 'KTN07'],
            ['state_code' => 'KTN', 'name' => 'Bachok', 'district_code' => 'KTN08'],
            ['state_code' => 'KTN', 'name' => 'Pasir Puteh', 'district_code' => 'KTN09'],
            ['state_code' => 'KTN', 'name' => 'Jeli', 'district_code' => 'KTN10'],

            // Kuala Lumpur (state_id: 4)
            ['state_code' => 'KUL', 'name' => 'Kuala Lumpur City Centre', 'district_code' => 'KUL01'],
            ['state_code' => 'KUL', 'name' => 'Cheras', 'district_code' => 'KUL02'],
            ['state_code' => 'KUL', 'name' => 'Kepong', 'district_code' => 'KUL03'],
            ['state_code' => 'KUL', 'name' => 'Petaling', 'district_code' => 'KUL04'],
            ['state_code' => 'KUL', 'name' => 'Setiawangsa', 'district_code' => 'KUL05'],
            ['state_code' => 'KUL', 'name' => 'Titiwangsa', 'district_code' => 'KUL06'],
            ['state_code' => 'KUL', 'name' => 'Wangsa Maju', 'district_code' => 'KUL07'],
            ['state_code' => 'KUL', 'name' => 'Lembah Pantai', 'district_code' => 'KUL08'],
            ['state_code' => 'KUL', 'name' => 'Segambut', 'district_code' => 'KUL09'],
            ['state_code' => 'KUL', 'name' => 'Bangsar', 'district_code' => 'KUL10'],
            ['state_code' => 'KUL', 'name' => 'Bukit Bintang', 'district_code' => 'KUL11'],

            // Labuan (state_id: 5)
            ['state_code' => 'LBN', 'name' => 'Labuan', 'district_code' => 'LBN01'],

            // Melaka (state_id: 5) - Fixed comment
            ['state_code' => 'MLK', 'name' => 'Melaka Tengah', 'district_code' => 'MLK01'],
            ['state_code' => 'MLK', 'name' => 'Alor Gajah', 'district_code' => 'MLK02'],
            ['state_code' => 'MLK', 'name' => 'Jasin', 'district_code' => 'MLK03'],

            // Negeri Sembilan (state_id: 7)
            ['state_code' => 'NSN', 'name' => 'Seremban'],
            ['state_code' => 'NSN', 'name' => 'Port Dickson'],
            ['state_code' => 'NSN', 'name' => 'Rembau'],
            ['state_code' => 'NSN', 'name' => 'Kuala Pilah'],
            ['state_code' => 'NSN', 'name' => 'Jelebu'],
            ['state_code' => 'NSN', 'name' => 'Tampin'],
            ['state_code' => 'NSN', 'name' => 'Jempol'],

            // Pahang (state_id: 8)
            ['state_code' => 'PHG', 'name' => 'Kuantan'],
            ['state_code' => 'PHG', 'name' => 'Temerloh'],
            ['state_code' => 'PHG', 'name' => 'Bentong'],
            ['state_code' => 'PHG', 'name' => 'Raub'],
            ['state_code' => 'PHG', 'name' => 'Jerantut'],
            ['state_code' => 'PHG', 'name' => 'Maran'],
            ['state_code' => 'PHG', 'name' => 'Pekan'],
            ['state_code' => 'PHG', 'name' => 'Rompin'],
            ['state_code' => 'PHG', 'name' => 'Kuala Lipis'],
            ['state_code' => 'PHG', 'name' => 'Bera'],
            ['state_code' => 'PHG', 'name' => 'Cameron Highlands'],

            // Penang (state_id: 9)
            ['state_code' => 'PNG', 'name' => 'George Town'],
            ['state_code' => 'PNG', 'name' => 'Seberang Perai Utara'],
            ['state_code' => 'PNG', 'name' => 'Seberang Perai Tengah'],
            ['state_code' => 'PNG', 'name' => 'Seberang Perai Selatan'],
            ['state_code' => 'PNG', 'name' => 'Timur Laut'],

            // Perak (state_id: 10)
            ['state_code' => 'PRK', 'name' => 'Ipoh'],
            ['state_code' => 'PRK', 'name' => 'Taiping'],
            ['state_code' => 'PRK', 'name' => 'Kuala Kangsar'],
            ['state_code' => 'PRK', 'name' => 'Teluk Intan'],
            ['state_code' => 'PRK', 'name' => 'Tanjung Malim'],
            ['state_code' => 'PRK', 'name' => 'Lumut'],
            ['state_code' => 'PRK', 'name' => 'Parit Buntar'],
            ['state_code' => 'PRK', 'name' => 'Seri Iskandar'],
            ['state_code' => 'PRK', 'name' => 'Bagan Serai'],
            ['state_code' => 'PRK', 'name' => 'Kampar'],
            ['state_code' => 'PRK', 'name' => 'Batu Gajah'],
            ['state_code' => 'PRK', 'name' => 'Gerik'],

            // Perlis (state_id: 11)
            ['state_code' => 'PLS', 'name' => 'Kangar'],
            ['state_code' => 'PLS', 'name' => 'Arau'],
            ['state_code' => 'PLS', 'name' => 'Padang Besar'],

            // Putrajaya (state_id: 12)
            ['state_code' => 'PJY', 'name' => 'Putrajaya', 'district_code' => 'PJY01'],

            // Sabah (state_id: 13)
            ['state_code' => 'SBH', 'name' => 'Kota Kinabalu'],
            ['state_code' => 'SBH', 'name' => 'Sandakan'],
            ['state_code' => 'SBH', 'name' => 'Tawau'],
            ['state_code' => 'SBH', 'name' => 'Lahad Datu'],
            ['state_code' => 'SBH', 'name' => 'Keningau'],
            ['state_code' => 'SBH', 'name' => 'Putatan'],
            ['state_code' => 'SBH', 'name' => 'Donggongon'],
            ['state_code' => 'SBH', 'name' => 'Semporna'],
            ['state_code' => 'SBH', 'name' => 'Kudat'],
            ['state_code' => 'SBH', 'name' => 'Papar'],
            ['state_code' => 'SBH', 'name' => 'Ranau'],
            ['state_code' => 'SBH', 'name' => 'Beaufort'],
            ['state_code' => 'SBH', 'name' => 'Kota Marudu'],
            ['state_code' => 'SBH', 'name' => 'Kuala Penyu'],
            ['state_code' => 'SBH', 'name' => 'Sipitang'],
            ['state_code' => 'SBH', 'name' => 'Tenom'],
            ['state_code' => 'SBH', 'name' => 'Kunak'],
            ['state_code' => 'SBH', 'name' => 'Kalabakan'],
            ['state_code' => 'SBH', 'name' => 'Tongod'],
            ['state_code' => 'SBH', 'name' => 'Kinabatangan'],
            ['state_code' => 'SBH', 'name' => 'Beluran'],
            ['state_code' => 'SBH', 'name' => 'Telupid'],
            ['state_code' => 'SBH', 'name' => 'Pitas'],
            ['state_code' => 'SBH', 'name' => 'Kota Belud'],
            ['state_code' => 'SBH', 'name' => 'Tuaran'],
            ['state_code' => 'SBH', 'name' => 'Penampang'],

            // Sarawak (state_id: 14)
            ['state_code' => 'SWK', 'name' => 'Kuching'],
            ['state_code' => 'SWK', 'name' => 'Sibu'],
            ['state_code' => 'SWK', 'name' => 'Miri'],
            ['state_code' => 'SWK', 'name' => 'Bintulu'],
            ['state_code' => 'SWK', 'name' => 'Limbang'],
            ['state_code' => 'SWK', 'name' => 'Sarikei'],
            ['state_code' => 'SWK', 'name' => 'Kapit'],
            ['state_code' => 'SWK', 'name' => 'Samarahan'],
            ['state_code' => 'SWK', 'name' => 'Sri Aman'],
            ['state_code' => 'SWK', 'name' => 'Betong'],
            ['state_code' => 'SWK', 'name' => 'Mukah'],
            ['state_code' => 'SWK', 'name' => 'Lawas'],

            // Selangor (state_id: 15)
            ['state_code' => 'SGR', 'name' => 'Shah Alam'],
            ['state_code' => 'SGR', 'name' => 'Petaling Jaya'],
            ['state_code' => 'SGR', 'name' => 'Subang Jaya'],
            ['state_code' => 'SGR', 'name' => 'Klang'],
            ['state_code' => 'SGR', 'name' => 'Kajang'],
            ['state_code' => 'SGR', 'name' => 'Ampang'],
            ['state_code' => 'SGR', 'name' => 'Selayang'],
            ['state_code' => 'SGR', 'name' => 'Rawang'],
            ['state_code' => 'SGR', 'name' => 'Sepang'],
            ['state_code' => 'SGR', 'name' => 'Puchong'],
            ['state_code' => 'SGR', 'name' => 'Seri Kembangan'],
            ['state_code' => 'SGR', 'name' => 'Kuala Selangor'],
            ['state_code' => 'SGR', 'name' => 'Sabak Bernam'],

            // Terengganu (state_id: 16)
            ['state_code' => 'TRG', 'name' => 'Kuala Terengganu'],
            ['state_code' => 'TRG', 'name' => 'Kemaman'],
            ['state_code' => 'TRG', 'name' => 'Dungun'],
            ['state_code' => 'TRG', 'name' => 'Marang'],
            ['state_code' => 'TRG', 'name' => 'Hulu Terengganu'],
            ['state_code' => 'TRG', 'name' => 'Setiu'],
            ['state_code' => 'TRG', 'name' => 'Besut'],
        ];

        foreach ($districts as $district) {
            $state = DB::table('states')->where('state_code', $district['state_code'])->first();
            
            if ($state) {
                DB::table('districts')->updateOrInsert(
                    ['state_id' => $state->id, 'name' => $district['name']],
                    [
                        'state_id' => $state->id,
                        'name' => $district['name'],
                        'district_code' => $district['district_code'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}