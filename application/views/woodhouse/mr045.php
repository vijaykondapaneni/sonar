<?php 
$this->load->view('users/headermenu');
error_reporting(E_ERROR | E_PARSE);?>
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
    <h1><?php echo ("Employee Sales Summary");?> - MR045</h1>
    <h4><?php echo $displaytime;?>
        <div class="pull-right"><button onclick="goBack()" class="btn btn-success">Back</button></div>
    </h4>
  </div>
</div>

<div class="container table-responsive" >
    <table class="table table-bordered table-hover">
        <thead>
            <tr class="bg-primary">
                <th class="">Services<br># Amount %</th>
                <th class="">Products<br># Amount %</th>
                <th class="">Tanning<br># Amount %</th>
                <th class="">Gift Certificate<br># Amount %</th>
                <th class="">Package/Series<br># Amount %</th>
                <th class="">Membership<br># Amount %</th>
                <th class="">Totals<br># Amount %</th>
            </tr>
        </thead>
        <tbody>
         <?php
          if (isset($all) and $all!=''){
            $totalemp_service = 0;
            $final_total_amount_row = 0;
            $total_service_nprice_qty = 0;
            $total_service_total = 0;
            $total_product_nprice_qty = 0;
            $total_product_total = 0;
            $total_giftcardscount=0;
            $total_giftcards_nprice_total_emp=0;
            $total_tanningcount=0;
            $total_tanning_nprice_total_emp=0;
            $total_packagecount=0;
            $total_package_nprice_total_emp=0;
            $total_membershipcount=0;
            $total_membership_nprice_total_emp=0;
            foreach ($all as $key => $value) {
            $iid = $value['iid'];
            if(is_array($value['cfirstname'])){
              $cfirstname = $value['cfirstname'][0];
            }else{
               $cfirstname = $value['cfirstname'];
            }
            $millMethodParams['XmlIds'] = '<NewDataSet><Ids><Id>'.$iid.'</Id></Ids></NewDataSet>';
            $millMethodParams['StartDate'] = $start_date;
            $millMethodParams['EndDate'] = $end_date;
            $millMethodParams['IncludeVoided'] = 0;
            // servicesales
            $servicesales = $this->nusoap_library->getMillMethodCall('GetServiceSalesByEmployee',$millMethodParams);

            /*$service_nprice = !empty($servicesales['ServiceSalesByEmployee']) ? 
                                array_column($servicesales['ServiceSalesByEmployee'], "nprice") : array();*/
            $service_nquantity = !empty($servicesales['ServiceSalesByEmployee']) ? 
                                array_column($servicesales['ServiceSalesByEmployee'], "nquantity") : array();
            $service_nprice_qty = array_sum($service_nquantity);
            
            //$service_total =  array_sum($service_nprice) * array_sum($service_nquantity);

            $service_total = 0;

            if(!empty($servicesales['ServiceSalesByEmployee'])){
                foreach ($servicesales['ServiceSalesByEmployee'] as $servicekey => $servicevalue) {
                    $service_total+=$servicevalue['nprice']*$servicevalue['nquantity'];
                }
            }

            $individual_emp_service = !empty($totalservicesamonut)?($service_total/$totalservicesamonut)*100:0;


            $total_service_nprice_qty+=$service_nprice_qty;
            $total_service_total+=$service_total;
            // productsales
            $productssales = $this->nusoap_library->getMillMethodCall('GetProductSalesByEmployee',$millMethodParams);
            /*$product_nprice = !empty($productssales['ProductSalesByEmployee']) ? 
                                array_column($productssales['ProductSalesByEmployee'], "nprice") : array();
            */
            $product_nquantity = !empty($productssales['ProductSalesByEmployee']) ? 
                                array_column($productssales['ProductSalesByEmployee'], "nquantity") : array();
            $product_nprice_qty = array_sum($product_nquantity); 
           /* $product_total =  array_sum($product_nprice) * array_sum($product_nquantity);
            
            $product_total = 0;*/
             $product_total = 0;
            if(!empty($productssales['ProductSalesByEmployee']))
            foreach ($productssales['ProductSalesByEmployee'] as $productkey => $productvalue) {
                $product_total+=$productvalue['nprice']*$productvalue['nquantity'];
            }

            $individual_emp_product = !empty($totalproductsamonut)?($product_total/$totalproductsamonut)*100:0;

            $total_product_nprice_qty+=$product_nprice_qty;
            $total_product_total+=$product_total;
            
            //giftcards
            $plus_staff = array('iempid'=>$iid);
            $giftcards_array = array_filter(
                                $totalgiftcards,
                                function ($key) use ($plus_staff) {
                                  return in_array($key["iempid"], $plus_staff);
                                });
            $giftcardscount = count($giftcards_array);
            $giftcards_nprice = 0;
            if(!empty($giftcards_array)){
                foreach ($giftcards_array as $gckey => $gcvalue) {
                    $giftcards_nprice+= $gcvalue['nprice'];
                }
            }


            /*$giftcards_nprice = !empty($giftcards_array) ? 
                                array_column($giftcards_array, "nprice") : array();*/
            //$giftcards_nprice_total_emp = array_sum($giftcards_nprice);
            $giftcards_nprice_total_emp = $giftcards_nprice;


            $individual_emp_giftcard = !empty($giftcards_nprice_total) ? ($giftcards_nprice_total_emp / $giftcards_nprice_total)*100:0;

            $total_giftcardscount+=$giftcardscount;
            $total_giftcards_nprice_total_emp+=$giftcards_nprice_total_emp;

            // tanning
            $plus_staff = array('iempid'=>$iid);
            $tanning_array = array_filter(
                                $totaltanning,
                                function ($key) use ($plus_staff) {
                                  return in_array($key["iempid"], $plus_staff);
                                });
            $tanningcount = count($tanning_array);
            $tanning_nprice = !empty($tanning_array) ? 
                                array_column($tanning_array, "nprice") : array();
            $tanning_nprice_total_emp = array_sum($tanning_nprice);
            
            $individual_emp_tanning = !empty($tanning_nprice_total) ? ($tanning_nprice_total_emp / $tanning_nprice_total)*100:0;

            $total_tanningcount+=$tanningcount;
            $total_tanning_nprice_total_emp+=$tanning_nprice_total_emp;

            // pacakge
            if(!empty($package_array)){

            $plus_staff = array('iempid'=>$iid);
            $package_array = array_filter(
                                $totalpackage,
                                function ($key) use ($plus_staff) {
                                  return in_array($key["iempid"], $plus_staff);
                                });
            }else{
              $package_array = array();
            }
            $packagecount = count($package_array);

            $package_nprice = !empty($package_array) ? 
                                array_column($package_array, "namount") : array();
            $package_nprice_total_emp = array_sum($package_nprice);
            $individual_emp_package = !empty($package_nprice_total) ? ($package_nprice_total_emp / $package_nprice_total)*100:0;

            $total_packagecount+=$packagecount;
            $total_package_nprice_total_emp+=$package_nprice_total_emp;

            // memberships
            $plus_staff = array('iempid'=>$iid);
            $Membership_array = array_filter(
                                $totalmemberships,
                                function ($key) use ($plus_staff) {
                                  return in_array($key["iempid"], $plus_staff);
                                });
            $membershipcount = count($Membership_array);

            $membership_nprice = !empty($Membership_array) ? 
                                array_column($Membership_array, "nprice") : array();
            $membership_nprice_total_emp = array_sum($membership_nprice);
            $individual_emp_membership = !empty($package_nprice_total) ? ($membership_nprice_total_emp / $package_nprice_total)*100:0;

            $total_membershipcount+=$membershipcount;
            $total_membership_nprice_total_emp+=$membership_nprice_total_emp;

            //total amount row

            $total_amount_row = $service_total+$product_total+$giftcards_nprice_total_emp+$tanning_nprice_total_emp+$package_nprice_total_emp+$membership_nprice_total_emp;

            $final_total_amount_row+=$total_amount_row;
            
            ?>

            <tr class="table-row" id="row" >
                <td>
                  <div align="center"><?php echo $value['clastname'];?>,<?php echo $cfirstname?></div>
                    <div style="float: left;width: 100%">
                     <div style="float: left;width:23%;font-size:13px;"><?php echo $service_nprice_qty;?></div>
                     <div style="float: left;width:53%;font-size:13px;">$<?php echo number_format($service_total,2);?></div>
                     <div style="float: left;width:24%;font-size:13px;"><?php echo  number_format($individual_emp_service);?>%</div>
                    </div>  
                </td>
                <td>
                  <div style="float: left;width: 100%">
                     <div style="float: left;width:23%;font-size:13px;"><?php echo $product_nprice_qty;?></div>
                     <div style="float: left;width:53%;font-size:13px;">$<?php echo number_format($product_total,2);?></div>
                     <div style="float: left;width:24%;font-size:13px;"><?php echo  number_format($individual_emp_product);?>%</div>
                  </div>  
                </td>
                 <td>
                  <div style="float: left;width: 100%">
                     <div style="float: left;width:23%;font-size:13px;"><?php echo $tanningcount;?></div>
                     <div style="float: left;width:53%;font-size:13px;">$<?php echo number_format($tanning_nprice_total_emp,2);?></div>
                     <div style="float: left;width:24%;font-size:13px;"><?php echo  number_format($individual_emp_tanning);?>%</div>
                  </div>  
                </td>
                 <td>
                  <div style="float: left;width: 100%">
                     <div style="float: left;width:23%;font-size:13px;"><?php echo $giftcardscount;?></div>
                     <div style="float: left;width:53%;font-size:13px;">$<?php echo number_format($giftcards_nprice_total_emp,2);?></div>
                     <div style="float: left;width:24%;font-size:13px;"><?php echo  number_format($individual_emp_giftcard);?>%</div>
                  </div>   
                </td>
                 <td>
                  <div style="float: left;width: 100%">
                     <div style="float: left;width:23%;font-size:13px;"><?php echo $packagecount;?></div>
                     <div style="float: left;width:53%;font-size:13px;">$<?php echo number_format($package_nprice_total_emp,2);?></div>
                     <div style="float: left;width:24%;font-size:13px;"><?php echo  number_format($individual_emp_package);?>%</div>
                  </div>  
                </td>
                 <td>
                  <div style="float: left;width: 100%">
                     <div style="float: left;width:23%;font-size:13px;"><?php echo $membershipcount;?></div>
                     <div style="float: left;width:53%;font-size:13px;">$<?php echo number_format($membership_nprice_total_emp,2);?></div>
                     <div style="float: left;width:24%;font-size:13px;"><?php echo  number_format($individual_emp_membership);?>%</div>
                  </div>  
                </td>
                 <td>
                  <div style="float: left;width: 100%">
                     <div style="float: left;width:50%;font-size:13px;">$<?php echo number_format($total_amount_row,2);?></div>
                   </div>  
                </td>
               </tr>
               <?php
          } ?>

          <tr class="bg-success">
                <th class="">Services<br># Amount %</th>
                <th class="">Products<br># Amount %</th>
                <th class="">Tanning<br># Amount %</th>
                <th class="">Gift Certificate<br># Amount %</th>
                <th class="">Package/Series<br># Amount %</th>
                <th class="">Membership<br># Amount %</th>
                <th class="">Totals<br># Amount %</th>
           </tr>
            <tr>
                <td class="">
                 <div style="float: left;width: 100%">
                     <div style="float: left;width:50%;font-size:13px;"><?php echo $total_service_nprice_qty;?></div>
                     <div style="float: left;width:50%;font-size:13px;">$<?php echo number_format($total_service_total,2);?></div>
                  </div>
                </td>
                <td class="">
                 <div style="float: left;width: 100%">
                     <div style="float: left;width:50%;font-size:13px;"><?php echo $total_product_nprice_qty;?></div>
                     <div style="float: left;width:50%;font-size:13px;">$<?php echo number_format($total_product_total,2);?></div>
                  </div>
                </td>
               
                <td class="">
                 <div style="float: left;width: 100%">
                     <div style="float: left;width:50%;font-size:13px;"><?php echo $total_tanningcount;?></div>
                     <div style="float: left;width:50%;font-size:13px;">$<?php echo number_format($total_tanning_nprice_total_emp,2);?></div>
                  </div>
                </td>
                 <td class="">
                 <div style="float: left;width: 100%">
                     <div style="float: left;width:50%;font-size:13px;"><?php echo $total_giftcardscount;?></div>
                     <div style="float: left;width:50%;font-size:13px;">$<?php echo number_format($total_giftcards_nprice_total_emp,2);?></div>
                  </div>
                </td>
                <td class="">
                 <div style="float: left;width: 100%">
                     <div style="float: left;width:50%;font-size:13px;"><?php echo $total_packagecount;?></div>
                     <div style="float: left;width:50%;font-size:13px;">$<?php echo number_format($total_package_nprice_total_emp,2);?></div>
                  </div>
                </td>
                <td class="">
                 <div style="float: left;width: 100%">
                     <div style="float: left;width:50%;font-size:13px;"><?php echo $total_membershipcount;?></div>
                     <div style="float: left;width:50%;font-size:13px;">$<?php echo number_format($total_membership_nprice_total_emp,2);?></div>
                  </div>
                </td>
                <td class="">
                 <div style="float: left;width: 100%">
                     <div style="float: left;width:50%;font-size:13px;">$<?php echo number_format($final_total_amount_row,2);?></div>
                    
                  </div>
                </td>
           </tr>

          <?php 
            $retail_to_service = !empty($total_product_total) ? ($total_product_total/$total_service_total)*100 :0;
            $retail_to_total = !empty($total_product_total)?($total_product_total/$final_total_amount_row)*100:0;
            $retail_ticket = !empty($total_product_total)? $total_product_total / $totalticketscount:0;
          ?>

           <table class="table table-bordered table-hover">
              <tr class="table-row bg-success" id="row">
                 <th>#Tickets : <?php echo $totalticketscount?></th>
                 <th>#Service Only Tickets:<?php echo $serviceInvoicescount?></th>
                 <th align="right">#Retail Only Tickets:<?php echo $productInvoicescount?></th>
              </tr>
              <tr class="table-row bg-success" id="row">
                 <td>Clients in Business: <?php echo $totalclients?></td>
                 <td colspan="2" align="right">Average Number of Services Per Visit:0</td>
              </tr>
               <tr class="table-row bg-success" id="row">
                 <td>Repeat Client Visits: <?php echo $totalrepeatedclients?></td>
                 <td colspan="2" align="right">Average Amount Spent(Retail + Service) Per Visit:$0</td>
              </tr>
              <tr class="table-row bg-success" id="row">
                 <td>New Client Visits: <?php echo $totalnewclients?></td>
                 <td colspan="2" align="right">Average Retail Units Per Visit:0</td>
              </tr>
              <tr class="table-row bg-success" id="row">
                 <td></td>
                 <td colspan="2" align="right">Percentage of Retail to Service Sales:<?php echo number_format($retail_to_service,2);?>%</td>
              </tr>
              <tr class="table-row bg-success" id="row">
                 <td></td>
                 <td colspan="2" align="right">Percentage of Retail To Total:<?php echo number_format($retail_to_total,2);?>%</td>
              </tr>
              <tr class="table-row bg-success" id="row">
                 <td></td>
                 <td colspan="2" align="right">Retail $/Ticket:$<?php echo number_format($retail_ticket,2)?></td>
              </tr>
           </table>

       <?php } 
    
    else {
      ?>
      <tr><td colspan="9"><div class="alert alert-warning"><?php print _("No Records Found.");?></div></td></tr>
      <?php
    }
    ?> 
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
