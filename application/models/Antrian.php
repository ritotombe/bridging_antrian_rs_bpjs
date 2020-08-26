<?php
class Antrian extends CI_Model
{
    public function auth($username, $password)
    {
        /* encode dulu username nya, karena di db di encrypt */
        $username = $username;
        $password = md5($password);
        $this->db->where('email', $username);
        $this->db->where('password', $password);
        $this->db->from('tbl_user');
        $query = $this->db->get();
        return $query->result();
    }

    public function cek_terdaftar($nomorkartu, $nik, $kodepoli, $tgl_periksa)
    {
        $this->db->where('nik', $nomorkartu);
        $this->db->or_where('nik', $nik);
        $this->db->where('id_poli', $kodepoli);
        $this->db->where('tgl_periksa', $tgl_periksa);
        $this->db->from('tbl_antrian');
        $query = $this->db->get();
        return $query->result();
    }

    public function cek_terdaftar_ref($nomorkartu, $nik, $kodepoli, $noreferensi)
    {
        $this->db->where('nik', $nomorkartu);
        $this->db->or_where('nik', $nik);
        $this->db->where('id_poli', $kodepoli);
        $this->db->where('no_referensi', $noreferensi);
        $this->db->from('tbl_antrian');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_poli($kodepoli)
    {
        $this->db->where('BPJS_kode_poli', $kodepoli);
        $this->db->from('tbl_poliklinik');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_jadwal($kodepoli, $hari)
    {
        $this->db->where('id_poliklinik', $kodepoli);
        $this->db->where('hari', $hari);
        $this->db->from('tbl_jadwal_praktek_dokter');
        $query = $this->db->get();
        return $query->result(); 
    }

    public function input($no_antrian, $nomorkartu, $nik, $notelp, $tanggalperiksa, $kodepoli, $nomorreferensi, $jenisreferensi, $jenisrequest, $polieksekutif, $id_jadwal)
    {
        $data = array(
            'no_antrian' => $no_antrian,
            'no_peserta' => $nomorkartu,
            'nik' => $nik,
            // 'notelp' => $notelp,
            'id_jadwal'=> $id_jadwal,
            'tgl_periksa' => $tanggalperiksa,
            'id_poli' => $kodepoli,
            'no_referensi' => $nomorreferensi,
            'jns_referensi' => $jenisreferensi,
            'jns_req' => $jenisrequest,
            'poli_eksekutif' => $polieksekutif,
        );
        $this->db->insert('tbl_antrian', $data);
        $insert_id = $this->db->insert_id();
        return  $insert_id;
    }

    public function get_libur($tanggalperiksa){
        $hari_libur = array(
            "2020-10-29",
            "2020-12-25",
            "2020-10-30",
            "2020-10-24",
        );

        return in_array($tanggalperiksa, $hari_libur);
    }

    public function get_estimasi($kodepoli, $tanggalperiksa, $jammulai=NULL)
    {
        /* perhitungan estimasi disesuaikan sendiri dengan sistem antrian RS */

        // $this->db->select('count(*) as jml');
        // $this->db->where('id_poli', $kodepoli);
        // $this->db->where('tgl_periksa', $tanggalperiksa);
        // $this->db->where('poli_eksekutif', '0');
        // $this->db->where('status_antrian', 2);
        // $this->db->group_by('status_antrian');
        // $this->db->from('tbl_antrian');
        // $query = $this->db->get();
        // $total antrian $query->result();

        if ($jammulai){
            date_default_timezone_set('Asia/Makassar');
            $stamp = strtotime($tanggalperiksa." ".$jammulai);
            $time_in_ms = $stamp * 1000;
            return $time_in_ms;
        }

        date_default_timezone_set('Asia/Makassar');
        $stamp = strtotime($tanggalperiksa);
        $time_in_ms = $stamp * 1000;
        return $time_in_ms;
    }

    public function get_antrian_terakhir($kodepoli, $tanggalperiksa)
    {
        $this->db->select('no_antrian');
        $this->db->where('id_poli', $kodepoli);
        $this->db->where('tgl_periksa', $tanggalperiksa);
        $this->db->order_by('no_antrian', 'DESC');
        $this->db->limit(1);
        $this->db->from('tbl_antrian');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_dilayani($kodepoli, $tanggalperiksa, $layan = '2')
    {
        $this->db->select('count(*) as jml');
        $this->db->where('id_poli', $kodepoli);
        $this->db->where('tgl_periksa', $tanggalperiksa);
        $this->db->where('poli_eksekutif', '0');
        $this->db->where('status_antrian', $layan);
        $this->db->group_by('status_antrian');
        $this->db->from('tbl_antrian');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_kodebooking_op($nopeserta)
    {
        $this->db->join('tbl_poliklinik', 'tbl_poliklinik.BPJS_kode_poli = tbl_operasi.kodepoli');
        $this->db->where('nopeserta', $nopeserta);
        $this->db->where('terlaksana', 0);
        $this->db->from('tbl_operasi');
        $query = $this->db->get();
        return $query->result();
    }

    public function get_list_op($tanggal_awal, $tanggal_akhir)
    {
        $this->db->join('tbl_poliklinik', 'tbl_poliklinik.BPJS_kode_poli = tbl_operasi.kodepoli');
        $this->db->where('tanggaloperasi >= ', $tanggal_awal);
        $this->db->where('tanggaloperasi <= ', $tanggal_akhir);
        $this->db->from('tbl_operasi');
        $query = $this->db->get();
        return $query->result();
    }
}
