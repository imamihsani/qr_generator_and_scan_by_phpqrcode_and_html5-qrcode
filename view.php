<?php
<?php defined('BASEPATH') or exit('No direct script access allowed'); 
/**
@property format $format
*/
?>
<style>
  .toolbar {
    float: left;
}


</style>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

<section class="content">
    <div class="container-fluid">
      <div class="card">
       <div class="card-body">
        <div class="row" style="align-items:center;">
            <div class="col-12 col-md-8 col-lg-6 offset-lg-3">
            <div id="scanner" class="w-100" style="overflow:hidden; align-items:center;"></div>
            <div class="d-flex justify-content-center" style="overflow:hidden; align-items:center;">
                <button class="btn btn-sm" id="btncontolStart"></button>
                <button class="btn btn-sm" id="btncontolStop"></button>
            </div>
            <div id="scan-result" class="mt-3" style="display:none;"></div>
            </div>
            <audio id="success-beep" src="<?= base_url('assets/sounds/beep.mp3') ?>" preload="auto"></audio> <!--suara beep biar kaya scan beneran-->
        </div>
       </div>
      </div>
    </div>           
      <!-- Main row -->
      
      <!-- /.row (main row) -->
    </div><!-- /.container-fluid -->
</section>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", async function() {

    const beepSound = document.getElementById('success-beep');
    let html5QrCode = new Html5Qrcode("scanner");
   
    //kalau scannya berhasil/sukses 
    function onScanSuccess(decodedText, decodedResult) {
        if (!isScanning) return;

        beepSound.currentTime = 0;
        beepSound.play();

        $.ajax({
            url: "<?= base_url('common/commondatabaseaset/scan?user_token=') . $this->session->userdata('user_token'); ?>",
            type: "POST",
            data: { kode_aktiva: decodedText },
            dataType: "json",
            success: function(res) {
                if (res.status === "success") {

                    let d = res.data;

                    let html = `
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Kode Aktiva</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['kode_aktiva']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Serial Number</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Serial Number']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Kelompok Aktiva</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Pengelompokan Aktiva Tetap']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Jenis Aktiva</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Jenis Aktiva Tetap']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Merek</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Merek']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Tipe</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Tipe']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Status</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Status']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Kondisi Aktiva</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Kondisi Aktiva']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Jumlah</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Jumlah']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">Cabang</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Cabang']}">
                        </div>
                        <div class="row mb-1 align-items-center">
                            <div class="col-5 col-md-4 font-weight-bold">PIC</div>
                            <input type="text" readonly class="form-control form-control-sm col-7 col-md-8" value="${d['Penanggung Jawab']}">
                        </div>
                    `;

                    $("#scan-result").html(html).show();

                } else if (res.status === "notfound") {
                    $("#scan-result").html(`<div class="text-danger">Aset tidak ditemukan.</div>`).show();
                } else {
                    $("#scan-result").html(`<div class="text-danger">Insert scan gagal.</div>`).show();
                }
            },
            error: function() {
                $("#scan-result").html(`<div class="text-danger">Terjadi kesalahan pada server.</div>`).show();
            }
        });
    }

    function onScanFailure(error) {
        // kalau gagal gausah diapa2in
    }

    let cameraId = null;
    let isScanning = false;

    Html5Qrcode.getCameras().then(async cameras => {
        if (!cameras || cameras.length === 0) {
            alert("Kamera tidak ditemukan.");
            return;
        }

        // wajib pakai kamera belakang
        let backCamera = cameras.find(c =>
            c.label.toLowerCase().includes("back")
        );

        cameraId = backCamera ? backCamera.id : cameras[0].id;

        //bikin tombol 
        const divControlStart = document.getElementById("btncontolStart");
        const divControlStop  = document.getElementById("btncontolStop");
        divControlStart.insertAdjacentHTML("beforeend", `
            <div style="text-align:center;">
                <button id="start-scan" class="btn btn-success btn-sm">
                    Start Scan
                </button>
            </div>
        `);
        divControlStop.insertAdjacentHTML("beforeend", `
            <div style="text-align:center;">
                <button id="stop-scan" class="btn btn-danger btn-sm" style="display:none;">
                    Stop Scan
                </button>
            </div>
        `);

        //start scan
        document.getElementById("start-scan").addEventListener("click", async function () {

            if (isScanning) return;

            isScanning = true;
            $("#scan-result").hide();

            await html5QrCode.start(
                { deviceId: 
                    { exact: cameraId } //hp samsung/iphone wajib pakai ini
                }, 
                {   fps: 4, 
                    qrbox: 310, //ini please jangan diubah2 lagi, kalau qr box lebih besar dari ini jadi bikin ga bisa scan di hp
                    aspectRatio: 1.333, //wajib buat hp samsung/iphone
                    videoConstraints: {
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        facingMode: "environment"
                    }
                },
                onScanSuccess,
                onScanFailure
            );

            document.getElementById("start-scan").style.display = "none";
            document.getElementById("stop-scan").style.display  = "inline-block";
        });

        //stop scan
        document.getElementById("stop-scan").addEventListener("click", function () {
            html5QrCode.stop().then(() => {
                isScanning = false;
                $("#scan-result").hide();
                document.getElementById("start-scan").style.display = "inline-block";
                document.getElementById("stop-scan").style.display  = "none";
            });
        });

    }).catch(err => {
        console.error("Camera error:", err);
    });


});
</script>
