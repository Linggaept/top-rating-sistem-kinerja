<?php

class perhitungan_Model extends CI_Model
{
    //Akun Aktif
    public function admin_Active()
    {
        return $this->db->get_where('admin', ['email' => $this->session->userdata('email')])->row_array();
    }

    public function tahun_Active()
    {
        return $this->db->get_where('periode', ['aktif' => 'Y'])->row_array();
    }

    public function get_AllGuru($tahun)
    {
        // Mengambil daftar guru unik yang memiliki entri di tabel 'nilai' untuk periode yang diberikan.
        // Query asli salah karena tabel 'penilaian' tidak secara langsung berisi 'id_guru' atau 'id_periode'.
        $query = "SELECT DISTINCT g.id_guru, g.nama_guru, g.nip
                  FROM `guru` g
                  JOIN `nilai` n ON g.id_guru = n.id_guru
                  WHERE n.id_periode = ?";
        return $this->db->query($query, $tahun)->result_array();
    }

    public function get_AllKriteria()
    {
        return $this->db->get('kriteria')->result_array();
    }

    // Fungsi ini digunakan untuk menampilkan semua nilai terhitung. Ini perlu menggabungkan guru, nilai, dan penilaian.
    public function get_AllNilai($tahun)
    {
        $query = "SELECT
                    p.id_penilaian,
                    g.nama_guru,
                    g.nip,
                    n.id_kriteria,
                    n.id_guru,
                    p.nilai,
                    p.normalisasi,
                    p.terbobot
                  FROM `guru` g
                  JOIN `nilai` n ON g.id_guru = n.id_guru
                  JOIN `penilaian` p ON n.id_nilai = p.id_nilai
                  WHERE n.id_periode = ?";
        return $this->db->query($query, $tahun)->result_array();
    }

    // Fungsi ini digunakan untuk mendapatkan semua entri 'penilaian' untuk 'id_kriteria' dan 'id_periode' tertentu.
    public function get_Nilai($id_k, $tahun)
    {
        $query = "SELECT
                    p.id_penilaian,
                    p.nilai,
                    n.id_guru, -- Diperlukan id_guru untuk perhitungan selanjutnya
                    p.normalisasi,
                    p.terbobot
                  FROM `penilaian` p
                  JOIN `nilai` n ON p.id_nilai = n.id_nilai
                  WHERE n.id_kriteria = ? AND n.id_periode = ?";
        return $this->db->query($query, array($id_k, $tahun))->result_array();
    }

    // Fungsi ini mendapatkan entri 'penilaian' tertentu untuk 'id_kriteria', 'id_guru', dan 'id_periode' yang diberikan.
    public function get_Nilai2($id_k, $id_g, $tahun)
    {
        $query = "SELECT
                    p.id_penilaian,
                    p.nilai,
                    p.normalisasi,
                    p.terbobot
                  FROM `penilaian` p
                  JOIN `nilai` n ON p.id_nilai = n.id_nilai
                  WHERE n.id_kriteria = ? AND n.id_guru = ? AND n.id_periode = ?";
        return $this->db->query($query, array($id_k, $id_g, $tahun))->row_array();
    }

    public function update_Normalisasi($normalisasi, $id_k, $id_g, $tahun)
    {
        $query = "UPDATE `penilaian` p
                  JOIN `nilai` n ON p.id_nilai = n.id_nilai
                  SET p.normalisasi = ?
                  WHERE n.id_kriteria = ? AND n.id_guru = ? AND n.id_periode = ?";
        $this->db->query($query, array($normalisasi, $id_k, $id_g, $tahun));
    }

    public function update_Terbobot($terbobot, $id_k, $id_g, $tahun)
    {
        $query = "UPDATE `penilaian` p
                  JOIN `nilai` n ON p.id_nilai = n.id_nilai
                  SET p.terbobot = ?
                  WHERE n.id_kriteria = ? AND n.id_guru = ? AND n.id_periode = ?";
        $this->db->query($query, array($terbobot, $id_k, $id_g, $tahun));
    }

    public function select_Max($id_k, $tahun)
    {
        $query = "SELECT MAX(p.terbobot) as nilai_a
                  FROM `penilaian` p
                  JOIN `nilai` n ON p.id_nilai = n.id_nilai
                  WHERE n.id_kriteria = ? AND n.id_periode = ?";
        return $this->db->query($query, array($id_k, $tahun))->row_array();
    }

    public function select_Min($id_k, $tahun)
    {
        $query = "SELECT MIN(p.terbobot) as nilai_a
                  FROM `penilaian` p
                  JOIN `nilai` n ON p.id_nilai = n.id_nilai
                  WHERE n.id_kriteria = ? AND n.id_periode = ?";
        return $this->db->query($query, array($id_k, $tahun))->row_array();
    }

    public function status_Periode($tahun)
    {
        $this->db->set('status', "Selesai");
        $this->db->where('id_periode', $tahun);
        $this->db->update('periode');
    }

    // --- Metode baru untuk ranking (dari analisis sebelumnya) ---

    public function save_topsis_results($results, $periode_id)
    {
        // Hapus hasil lama untuk periode ini untuk mencegah duplikasi
        $this->db->where('id_periode', $periode_id);
        $this->db->delete('hasil_topsis');

        foreach ($results as $result) {
            $data = [
                'id_guru' => $result['id_guru'],
                'id_periode' => $periode_id,
                'preferensi_score' => $result['preferensi_score'],
                'rank' => $result['rank'],
                'created_at' => date('Y-m-d H:i:s') // Tambahkan timestamp
            ];
            $this->db->insert('hasil_topsis', $data);
        }
    }

    public function delete_topsis_results($periode_id)
    {
        $this->db->where('id_periode', $periode_id);
        $this->db->delete('hasil_topsis');
    }

    public function get_ranked_results($periode_id)
    {
        $this->db->select('ht.rank, g.nama_guru, ht.preferensi_score');
        $this->db->from('hasil_topsis ht');
        $this->db->join('guru g', 'ht.id_guru = g.id_guru');
        $this->db->where('ht.id_periode', $periode_id);
        $this->db->order_by('ht.rank', 'ASC');
        return $this->db->get()->result_array();
    }
}
