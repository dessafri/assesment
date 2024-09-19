<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Relasi_jabatan_m extends MY_Model
{

    protected $_table_name = 'relasi_jabatan';
    protected $_primary_key = 'relasi_jabatan.Id';
    protected $_primary_filter = 'intval';
    protected $_order_by = "roll asc";

    public function __construct()
    {
        parent::__construct();
    }

    public function insert_relasi_jabatan($data)
    {
        // Ensure $data is an array of arrays
        if (!empty($data) && is_array($data[0])) {
            // Insert batch data
            $this->db->insert_batch('relasi_jabatan', $data);
            // Check if rows were affected
            return $this->db->affected_rows() > 0;
        } else {
            log_message('error', 'Invalid data format for insert_batch: ' . print_r($data, true));
            return false;
        }
    }

    public function delete_relation($id)
	{
		$query = "DELETE FROM relasi_jabatan WHERE user = '$id'";
        $this->db->query($query);
	}

    public function general_get_student_with_relation($id)
{
    // Memilih kolom yang diperlukan
    $this->db->select('student.name, student.studentID, relasi_jabatan.keterangan,relasi_jabatan.Id');

    // Melakukan join ke tabel student
    $this->db->join('student', 'student.studentID = relasi_jabatan.user_relation', 'LEFT');

    // Menambahkan kondisi WHERE pada tabel relasi_jabatan berdasarkan kolom user
    $this->db->where('relasi_jabatan.user', $id);

    // Mendapatkan hasil query dari tabel relasi_jabatan
    $query = $this->db->get('relasi_jabatan');

    return $query->result();
}
public function get_relasi_employee($id, $keterangan)
{
    // Memilih kolom yang diperlukan
    $this->db->select('student.name, student.studentID, relasi_jabatan.keterangan, relasi_jabatan.Id');

    // Melakukan join ke tabel student
    $this->db->join('student', 'student.studentID = relasi_jabatan.user_relation', 'LEFT');

    // Menambahkan kondisi WHERE pada tabel relasi_jabatan berdasarkan kolom user dan keterangan
    $this->db->where('relasi_jabatan.user', $id);
    $this->db->where('relasi_jabatan.keterangan', $keterangan);

    // Mendapatkan hasil query dari tabel relasi_jabatan
    $query = $this->db->get('relasi_jabatan');

    return $query->result();
}



}
