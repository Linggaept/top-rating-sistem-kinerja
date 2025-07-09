<?php
defined('BASEPATH') or exit('No direct script access allowed');

class perhitungan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('email')) {
            redirect('auth');
        }
        $this->load->model('perhitungan_model');
    }

    public function index()
    {
        $data['admin'] = $this->perhitungan_model->admin_Active();
        $data['title'] = 'Penilaian Kinerja Guru - Perhitungan';
        $data['position'] = 'Perhitungan';
        $data['periode'] = $this->perhitungan_model->tahun_Active();
        $tahun = $data['periode']['id_periode'];
        $data['guru'] = $this->perhitungan_model->get_AllGuru($tahun);
        $data['kriteria'] = $this->perhitungan_model->get_AllKriteria();
        $data['nilai'] = $this->perhitungan_model->get_AllNilai($tahun);

        $posisi = 0;
        foreach ($data['kriteria'] as $k) {
            $id_k = $k['id_kriteria'];
            //Nilai A+
            if ($k['jenis'] == 'Benefit') {
                $data['aplus'] = $this->perhitungan_model->select_Max($id_k, $tahun);
            } else {
                $data['aplus'] = $this->perhitungan_model->select_Min($id_k, $tahun);
            }
            $data['A_plus'][$posisi] = $data['aplus'];


            //Nilai A-
            if ($k['jenis'] == 'Benefit') {
                $data['amin'] = $this->perhitungan_model->select_Min($id_k, $tahun);
            } else {
                $data['amin'] = $this->perhitungan_model->select_Max($id_k, $tahun);
            }
            $data['A_min'][$posisi] = $data['amin'];

            $posisi = $posisi + 1;
        }

        $x = 0;
        foreach ($data['guru'] as $g) {
            $id_g = $g['id_guru'];
            $y = 0;
            $dplus = 0;
            $dmin = 0;
            foreach ($data['kriteria'] as $k) {
                $id_k = $k['id_kriteria'];
                $data['terbobot'] = $this->perhitungan_model->get_Nilai2($id_k, $id_g, $tahun);
                $n_terbobot = $data['terbobot']['terbobot'];
                $aplus = $data['A_plus'][$y]['nilai_a'];
                $amin = $data['A_min'][$y]['nilai_a'];

                //Nilai D+
                $n_dplus = number_format(pow($aplus - $n_terbobot, 2), 3);
                $dplus = $dplus + $n_dplus;

                //Nilai D-
                $n_dmin = number_format(pow($n_terbobot - $amin, 2), 3);
                $dmin = $dmin + $n_dmin;

                $y = $y + 1;
            }
            $data['hasil'][$x]['0'] =  number_format(sqrt($dplus), 3);
            $data['hasil'][$x]['1'] =  number_format(sqrt($dmin), 3);

            //Nilai Preferensi
            if (($data['hasil'][$x]['1'] + $data['hasil'][$x]['0']) != 0) { // Kondisi yang benar untuk mencegah pembagian dengan nol
                $preferensi = number_format($data['hasil'][$x]['1'] / ($data['hasil'][$x]['1'] + $data['hasil'][$x]['0']), 3);
            } else {
                $preferensi = 0;
                $this->session->set_flashdata('belum', 'Penilaian belum dihitung');
            }
            $data['hasil'][$x]['2'] =  $preferensi;
            $x = $x + 1;
        }

        $this->load->view('template/header', $data);
        $this->load->view('perhitungan/index', $data);
        $this->load->view('template/footer');
    }

    public function ranking()
    {
        $data['admin'] = $this->perhitungan_model->admin_Active();
        $data['title'] = 'Penilaian Kinerja Guru - Ranking';
        $data['position'] = 'Perhitungan'; // Atau 'Ranking' jika ingin status aktif yang berbeda

        $data['periode'] = $this->perhitungan_model->tahun_Active();
        $tahun = $data['periode']['id_periode'];

        $data['ranked_teachers'] = $this->perhitungan_model->get_ranked_results($tahun);

        $this->load->view('template/header', $data);
        $this->load->view('perhitungan/ranking', $data);
        $this->load->view('template/footer');
    }

    public function hitung()
    {
        $data['periode'] = $this->perhitungan_model->tahun_Active();
        $tahun = $data['periode']['id_periode'];
        $data['guru'] = $this->perhitungan_model->get_AllGuru($tahun);
        $data['kriteria'] = $this->perhitungan_model->get_AllKriteria();

        foreach ($data['kriteria'] as $k) {
            $id_k = $k['id_kriteria'];
            // Nilai Pembagi
            $data['nilai'] = $this->perhitungan_model->get_Nilai($id_k, $tahun);
            $pembagi = 0;
            foreach ($data['guru'] as $g) {
                $id_g = $g['id_guru'];
                $data['nilai2'] = $this->perhitungan_model->get_Nilai2($id_k, $id_g, $tahun);
                $nilai = number_format(pow($data['nilai2']['nilai'], 2), 3);
                $pembagi = $pembagi + $nilai;
            }
            $pembagi = number_format(sqrt($pembagi), 6);

            //Nilai Normalisasi
            foreach ($data['guru'] as $g) {
                $id_g = $g['id_guru'];
                $data['nilai2'] = $this->perhitungan_model->get_Nilai2($id_k, $id_g, $tahun);

                $nilai = $data['nilai2']['nilai'];
                if ($nilai and $pembagi != 0) {
                    $normalisasi = number_format($nilai / $pembagi, 3);
                } else {
                    $this->session->set_flashdata('kosong', 'Isi data yang masih kosong ');
                    redirect('perhitungan');
                }
                $this->perhitungan_model->update_Normalisasi($normalisasi, $id_k, $id_g, $tahun);
            }

            //Nilai Terbobot
            foreach ($data['guru'] as $g) {
                $id_g = $g['id_guru'];
                $data['nilai2'] = $this->perhitungan_model->get_Nilai2($id_k, $id_g, $tahun);
                $n_normalisasi = $data['nilai2']['normalisasi'];
                $bobot = $k['bobot'];
                $terbobot = number_format($n_normalisasi * $bobot, 3);
                $this->perhitungan_model->update_Terbobot($terbobot, $id_k, $id_g, $tahun);
            }
        }
        
        // --- Mulai: Perhitungan TOPSIS Akhir dan Penyimpanan Ranking ---
        // Ambil ulang data setelah normalisasi dan terbobot diperbarui
        $data['guru'] = $this->perhitungan_model->get_AllGuru($tahun);
        $data['kriteria'] = $this->perhitungan_model->get_AllKriteria();

        $posisi = 0;
        $A_plus = [];
        $A_min = [];
        foreach ($data['kriteria'] as $k) {
            $id_k = $k['id_kriteria'];
            // Nilai A+
            if ($k['jenis'] == 'Benefit') {
                $aplus_val = $this->perhitungan_model->select_Max($id_k, $tahun);
            } else {
                $aplus_val = $this->perhitungan_model->select_Min($id_k, $tahun);
            }
            $A_plus[$posisi] = $aplus_val;

            // Nilai A-
            if ($k['jenis'] == 'Benefit') {
                $amin_val = $this->perhitungan_model->select_Min($id_k, $tahun);
            } else {
                $amin_val = $this->perhitungan_model->select_Max($id_k, $tahun);
            }
            $A_min[$posisi] = $amin_val;

            $posisi++;
        }

        $final_results = [];
        foreach ($data['guru'] as $g) {
            $id_g = $g['id_guru'];
            $nama_guru = $g['nama_guru']; // Diperbaiki: 'nama_guru' adalah nama kolom yang benar dari tabel 'guru'
            $y = 0;
            $dplus_sum = 0;
            $dmin_sum = 0;

            foreach ($data['kriteria'] as $k) {
                $id_k = $k['id_kriteria'];
                $terbobot_data = $this->perhitungan_model->get_Nilai2($id_k, $id_g, $tahun);
                $n_terbobot = $terbobot_data['terbobot'];

                $aplus = $A_plus[$y]['nilai_a'];
                $amin = $A_min[$y]['nilai_a'];

                $n_dplus = pow($aplus - $n_terbobot, 2);
                $dplus_sum += $n_dplus;

                $n_dmin = pow($n_terbobot - $amin, 2);
                $dmin_sum += $n_dmin;

                $y++;
            }

            $preferensi = 0;
            if ((sqrt($dplus_sum) + sqrt($dmin_sum)) != 0) {
                $preferensi = sqrt($dmin_sum) / (sqrt($dmin_sum) + sqrt($dplus_sum));
            }

            $final_results[] = [
                'id_guru' => $id_g,
                'preferensi_score' => $preferensi // Gunakan presisi penuh untuk sorting
            ];
        }

        // Urutkan hasil berdasarkan preferensi_score secara menurun
        usort($final_results, function($a, $b) {
            return $b['preferensi_score'] <=> $a['preferensi_score'];
        });

        // Tetapkan peringkat
        $rank = 1;
        foreach ($final_results as $key => $result) {
            $final_results[$key]['rank'] = $rank++;
            $final_results[$key]['preferensi_score'] = number_format($result['preferensi_score'], 5); // Format untuk tampilan
        }

        // Simpan hasil ke database
        $this->perhitungan_model->delete_topsis_results($tahun); // Hapus hasil lama untuk periode ini
        $this->perhitungan_model->save_topsis_results($final_results, $tahun); // Simpan hasil baru
        // --- Selesai: Perhitungan TOPSIS Akhir dan Penyimpanan Ranking ---

        $this->session->set_flashdata('done', 'Perhitungan berhasil diselesaikan dan ranking telah disimpan.');
        redirect('perhitungan/ranking'); // Arahkan ke halaman ranking
    }
}
