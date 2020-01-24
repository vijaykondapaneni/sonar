<?php 
$this->load->view('users/headermenu');
error_reporting(E_ERROR | E_PARSE);
?>
<script type="text/javascript" src="<?php print base_url();?>/assets/js/pace.min.js"></script>
<style type="text/css">
 .pace {
  -webkit-pointer-events: none;
  pointer-events: none;

  -webkit-user-select: none;
  -moz-user-select: none;
  user-select: none;

  z-index: 2000;
  position: fixed;
  margin: auto;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  height: 5px;
  width: 200px;
  background: #fff;
  border: 1px solid #29d;

  overflow: hidden;
}

.pace .pace-progress {
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  -ms-box-sizing: border-box;
  -o-box-sizing: border-box;
  box-sizing: border-box;

  -webkit-transform: translate3d(0, 0, 0);
  -moz-transform: translate3d(0, 0, 0);
  -ms-transform: translate3d(0, 0, 0);
  -o-transform: translate3d(0, 0, 0);
  transform: translate3d(0, 0, 0);

  max-width: 200px;
  position: fixed;
  z-index: 2000;
  display: block;
  position: absolute;
  top: 0;
  right: 100%;
  height: 100%;
  width: 100%;
  background: #29d;
}

.pace.pace-inactive {
  display: none;
}
</style>
<div class="container">
  <div class="cal-main-title">
    <center><h1>The Woodhouse Day Spa - Zionsville</h1></center>
    <h1><?php echo ("Sales Summary");?> - MR085</h1>
    <h4><?php echo $displaytime;?>
        <div class="pull-right"><button onclick="goBack()" class="btn btn-success">Back</button></div>
    </h4>
  </div>
</div>

<div class="container table-responsive" >
    <table class="table table-bordered table-hover">
        <thead>
            <tr class="bg-primary">
                <th class="">Item Detail </th>
                <th class=""># Sold</th>
                <th class="">Total Income</th>
            </tr>
        </thead>
        <tbody>
            <!-- Gift Cards -->
            <tr class="table-row" id="row" >
                <td colspan="3"><h4><b>Gift Certificates</b></h4></td>
            </tr>
            <tr>
                 <td colspan="3"><h4>*Gift Certificate Refund*</h4></td>
            </tr>
            <?php
                //pa($returngiftcards);
                $totalrefundgiftcardsqty = 0;
                $totalrefundgiftcardsamount = 0;
                if(!empty($returngiftcards)){
                    foreach ($returngiftcards as $refundGcKey => $refundGcValue) { 
                        $refund_amount = $refundGcValue['nrefamount'];
                        $refunfgcamountdisplay = '('.'$'.number_format(abs($refund_amount),2).')';
                        $totalrefundgiftcardsamount+=$refund_amount;
                        ?>
                     <tr>
                         <td>*Refund*:$<?php echo number_format($refund_amount,2)?> Gift Certificate</td>
                         <td>-1</td>
                         <td><?php echo $refunfgcamountdisplay;?></td>
                     </tr>    
                    <?php } 
                   ?>
                    <tr>
                        <td>*Gift Certificate Refund* subtotal</td>
                        <td>-<?php echo count($returngiftcards);?></td>
                        <td><b>$<?php echo number_format($totalrefundgiftcardsamount,2);?></b></td>
                    </tr>

            <?php } ?>
            <tr>
                 <td colspan="3"><h4>Gift Certificates </h4></td>
            </tr>
            <?php
                //pa($returngiftcards);
                $totalgiftcardsamount = 0;
                if(!empty($giftcards)){
                foreach ($giftcards as $gCKey => $gCValue) {
                        $gc_amount = $gCValue['nprice']; 
                        $totalgiftcardsamount+=$gCValue['nprice']; 
                        ?>
                     <tr>
                         <td>Gift Certificates $<?php echo number_format($gc_amount,2)?> Purchased</td>
                         <td>1</td>
                         <td><?php echo number_format($gc_amount,2)?></td>
                     </tr>    
                    <?php 
                } 
                ?>
                    <tr>
                        <td>Gift Certificates subtotal</td>
                        <td><?php echo count($giftcards);?></b></td>
                        <td>$<?php echo number_format($totalgiftcardsamount,2);?></td>
                    </tr>

            <?php } ?>
            <?php
             $total_gc_count = count($giftcards) - count($returngiftcards);
             $total_gc_amount = $totalgiftcardsamount-$totalrefundgiftcardsamount;

            ?>
             <tr>
                <td><b>Gift Certificates subtotal</b></td>
                <td><b><?php echo $total_gc_count;?></b></td>
                <td><b>$<?php echo number_format($total_gc_amount,2);?></b></td>
            </tr>
            <!-- GiftCards Close -->
            <!-- Packages/Series -->
            <tr class="table-row" id="row" >
                <td colspan="3"><h4><b>Packages/Series</b></h4></td>
            </tr>
            <tr>
                 <td colspan="3"><h4>Packages/Series </h4></td>
            </tr>
            <?php
                //pa($returngiftcards);
                $totalpackagesamount = 0;
                if(!empty($packages)){
                foreach ($packages as $packageKey => $packageValue) {
                        $package_amount = $packageValue['namount']; 
                        $totalpackagesamount+=$packageValue['namount']; 
                        ?>
                     <tr>
                         <td>Packages\Series Sale $<?php echo number_format($package_amount,2)?></td>
                         <td>1</td>
                         <td>$<?php echo number_format($package_amount,2)?></td>
                     </tr>    
                    <?php 
                } 
                ?>
                    <tr>
                        <td>Pacakges subtotal</td>
                        <td><b><?php echo count($packages);?></b></td>
                        <td><b>$<?php echo number_format($totalpackagesamount,2);?></b></td>
                    </tr>

            <?php } ?>
            <tr>
                 <td colspan="3"><h4>*Packages/Series Refund* <?php //pa($refundpackages);?> </h4></td>
            </tr>
            <?php

                $totalrefundpackagesqty = 0;
                $totalrefundpackagesamount = 0;
                if(!empty($refundpackages)){

                    foreach ($refundpackages as $refundPackageKey => $refundPackageValue) { 
                        $refund_package_amount = $refundPackageValue['nprice'];
                        $refunfpackageamountdisplay = '('.'$'.number_format(abs($refund_package_amount),2).')';
                        $totalrefundpackagesamount+=$refund_package_amount;
                        ?>
                     <tr>
                         <td>*Series Refunds*:$<?php echo number_format($refund_package_amount,2)?> </td>
                         <td>-1</td>
                         <td><?php echo $refunfpackageamountdisplay;?></td>
                     </tr>    
                    <?php } 
                   ?>
                    <tr>
                        <td>*Packages/Series* subtotal</td>
                        <td>-<?php echo count($refundpackages);?></td>
                        <td><b>$<?php echo number_format($totalrefundpackagesamount,2);?></b></td>
                    </tr>

            <?php } ?>
            <?php
             $total_packages_count = count($packages) - count($refundpackages);
             $total_packages_amount = $totalpackagesamount-$totalrefundpackagesamount;

            ?>
            <tr>
                <td><b>Packages/Series subtotal</b></td>
                <td><b><?php echo $total_packages_count;?></b></td>
                <td><b>$<?php echo number_format($total_packages_amount,2);?></b></td>
            </tr>
            <!-- Packages/Series Close -->



            <!-- Products -->
            <tr class="table-row" id="row" >
                <td colspan="3"><h4><b>Products</b></h4></td>
            </tr>
            <?php 
              //pa($productsales,'productsales');
               $productotalamount = 0;
              if(!empty($productsales)){
                 $totalquantity = 0;
                 foreach ($productsales as $pSaleskey => $pSalesValue) {
                    $qty = intval($pSalesValue['nquantity']);
                    $amount = $pSalesValue['nprice'] * $qty;
                    if($pSalesValue['lrefund']=='true'){
                        $productcode = '*REFUND*';
                        $amountdisplay = '('.'$'.number_format(abs($amount),2).')';
                    }else{
                        $productcode =  $pSalesValue['cproductcode'];
                        $amountdisplay = '$'.number_format($amount,2);
                    }
                    $totalquantity+=$qty;
                    $productotalamount+=$amount;
                 ?>
                 <tr>
                     <td><?php echo $productcode;?>:<?php echo $pSalesValue['cproductdescription'];?></td>
                     <td><?php echo $qty;?></td>
                     <td><?php echo $amountdisplay;?></td>
                 </tr>
                    
                 <?php } ?>
                 <tr>
                     <td><b>Products Subtotal</b></td>
                     <td><b><?php echo $totalquantity;?></b></td>
                     <td><b>$<?php echo number_format($productotalamount,2);?></b></td>
                 </tr>
            <?php  }
            ?>

             <tr class="table-row" id="row" >
                <td colspan="3"><h4><b>Services</b></h4></td>
            </tr>
            <?php 
              //pa($productsales,'productsales');
              $servicetotalamount = 0;
              if(!empty($servicesales)){
                 $servicequantity = 0;
                 foreach ($servicesales as $sSaleskey => $sSalesValue) {
                    $qty = intval($sSalesValue['nquantity']);
                    $amount = $sSalesValue['nprice'] * $qty;
                    if($sSalesValue['lrefund']=='true'){
                        $servicecode = '*REFUND*';
                        $amountdisplay = '('.'$'.number_format(abs($amount),2).')';
                    }else{
                        $servicecode =  $sSalesValue['cservicecode'];
                        $amountdisplay = '$'.number_format($amount,2);
                    }
                    $totalquantity+=$qty;
                    $servicetotalamount+=$amount;
                 ?>
                 <tr>
                     <td><?php echo $servicecode;?>:<?php echo $sSalesValue['cservicedescription'];?></td>
                     <td><?php echo $qty;?></td>
                     <td><?php echo $amountdisplay;?></td>
                 </tr>
                    
                 <?php } ?>
                 <tr>
                     <td><b>Services Subtotal</b></td>
                     <td><b><?php echo $totalquantity;?></b></td>
                     <td><b>$<?php echo number_format($servicetotalamount,2);?></b></td>
                 </tr>
            <?php  }
            ?>
            <?php
             $final_amount = $total_gc_amount+$total_packages_amount+$productotalamount+$servicetotalamount;
            ?>

            <tr>
             <td colspan="2" align="right"><b>TOTAL SALES</b></td>
             <td><b>$<?php print number_format($final_amount,2); ?></b></td>
            <tr>

        </tbody>
    </table>
</div>

</div>

<style type="text/css">
  .action_calbtns ul li a.btn_del {
    text-align: left;
    border-radius: 0px;
    border: 0px none;
  }
</style>
<script>
function goBack() {
    window.history.back();
}
</script>
