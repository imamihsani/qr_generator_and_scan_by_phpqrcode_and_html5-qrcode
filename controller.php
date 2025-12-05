<?php
use Dompdf\Dompdf;
use Dompdf\Options;
require_once APPPATH.'libraries/phpqrcode/qrlib.php';

class Commondatabaseaset extends Mx_Controller
{
    public $CI;

    protected $data = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model('ga/ga_model', 'ga');
        $this->load->model('hrd/hrd_model', 'hrd');
        $this->load->helper('qrcode');
    }

    function generateQRBase64($text, $size = 4){
        ob_clean();
        ob_start();      

        QRcode::png($text, null, QR_ECLEVEL_L, $size);

        $imageString = ob_get_contents();
        ob_end_clean();

        return 'data:image/png;base64,' . base64_encode($imageString);
    }
  
    public function exportqr(){
        $dompdf = new Dompdf(['isRemoteEnabled' => true]);

        $filter=array();
        $aset = $this->ga->getAsetForQR($filter);
        $qrData = [];

        foreach ($aset as $row) {

            ob_start();
            QRcode::png($row['kode_aktiva'], false, QR_ECLEVEL_L, 4);
            $imageString = ob_get_contents();
            ob_end_clean();

            // Encode ke base64
            $base64 = base64_encode($imageString);

            $qrData[] = [
                'kode_aktiva' => $row['kode_aktiva'],
                'qr_base64'   => 'data:image/png;base64,' . $base64
            ];
        }

        $data['qrlist'] = $qrData;

        $html = $this->load->view('common_database_aset_qr', $data, TRUE);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        //bikin page number
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->get_font("Arial", "normal");
        // Posisi 
        $canvas->page_text(
            550,       // X (kanan bawah),
            805,       // Y (bawah), 
            "{PAGE_NUM}/{PAGE_COUNT}",
            $font,
            10,
            [0, 0, 0]  // warna hitam
        );

        $dompdf->stream("qr-code_aset_PT_MSA.pdf", ["Attachment" => 0]);
    }

    public function more(){

        $this->load->view('common_database_aset_scan', $this->data, false);
    }

    public function scan(){
        header('Content-Type: application/json');

        $filter=array();

        $kode = $this->input->post('kode_aktiva');

        if (!$kode) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Kode aktiva tidak terbaca'
            ]);
            return;
        }

        // $data = [
        //     'kode_aktiva' => $kode,
        //     'waktu_scan'  => date('Y-m-d H:i:s')
        // ];

        // $scan = $this->ga->insertScanAset($data);

        $filter=array('kode_aktiva' => $kode);
        $detail = $this->ga->getAsetForQR($filter);

        if (!$detail) {
            echo json_encode([
                'status' => 'notfound',
                'message' => 'Aset tidak ditemukan'
            ]);
            return;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $detail[0]
        ]);
    }

}
