<?php
public function getAsetForQR($filter = []) {
		$DB2 = $this->load->database('old_db', TRUE);

		$where = [];

		$sql = "SELECT * FROM `ga - aset`";
		if (!empty($filter['kode_aktiva'])) {
			$kode = $DB2->escape($filter['kode_aktiva']);
			$where[] = "kode_aktiva = $kode";
		}

		if (!empty($where)) {
			$sql .= " WHERE " . implode(" AND ", $where);
		}

		$sql .= " ORDER BY `no` DESC";

		$query = $DB2->query($sql);
		return $query->result_array();
	}
