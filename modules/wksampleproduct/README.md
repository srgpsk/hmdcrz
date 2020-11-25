- Module V5.1.0 compatible with PrestaShop V1.7.x.x
- Module V1.1.0 compatible with PrestaShop V1.6.x.x

### User Guide Documentation:
https://webkul.com/blog/prestashop-sample-product/

### Support Policy:
https://store.webkul.com/support.html/

### RefundPolicy:
https://store.webkul.com/refund-policy.html/

### Install note:
- If you want to sell sample of virtual products also, add a new custom Hook in /controllers/front/GetFileController.php for downloading virtual product sample:
    * Just before line: *$mimeType = false;* Add this line -

    `HOOK::exec('actionSampleProductDownloadBefore', array($info, &$file, &$filename));
    if (!$filename) {
        ?>
        <script type="text/javascript">
            alert("<?php echo $file ?>");
            history.back();
        </script>
        <?php
        exit();
    }`
