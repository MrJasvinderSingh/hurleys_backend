<!-- footer -->
<div class="footer">
  <div class="footer-inner">
    <div class="footer-part1">
      <h4><?=$langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></h4>
      <p><?= $COMPANY_ADDRESS?></p>
      <span>
        <p><b>P :</b>+<?= $SUPPORT_PHONE;?></p>
        <p><b>E :</b><?= $SUPPORT_MAIL;?></p>
      </span>
    </div>
    <div class="footer-part2">
      <h4><?=$langage_lbl['LBL_FOOTER_HOME_RESTAURANT_TXT']; ?></h4>
      <ul>
        <li><a href="contact-us"><?=$langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>
        <li><a href="about"><?=$langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a></li>
       <?php /* 
       <li><a href="help-center"><?=$langage_lbl['LBL_FOOTER_HOME_HELP_CENTER']; ?></a></li> */ ?>
        <!--<li><a href="http://hurleys.anviam.in:8086/become_a_delivery_partner.php" style="text-transform: capitalize;"><?=$langage_lbl['LBL_BECOME_A_DRIVER']; ?></a></li>

     <li><a href="http://hurleys.anviam.in:8086/become_a_restaurant_partner.php" style="text-transform: capitalize;">Become A Restaurant Partner</a></li>-->
      </ul>
    </div>
    <div class="footer-part3">
      <h4><?=$langage_lbl['LBL_OTHER_PAGE_FOOTER']; ?></h4>
      <ul>
        <?php /*
        <li><a href="how-it-works"><?=$langage_lbl['LBL_HOW_IT_WORKS']; ?></a></li>
        <li><a href="trust-safty-insurance"><?=$langage_lbl['LBL_SAFETY_AND_INSURANCE']; ?></a></li>*/ ?>
        <li><a href="terms-condition"><?=$langage_lbl['LBL_FOOTER_TERMS_AND_CONDITION']; ?></a></li>
        <li><a href="privacy-policy"><?=$langage_lbl['LBL_PRIVACY_POLICY_TEXT']; ?></a></li>
      </ul>
    </div>
    <div class="footer-part4">
	<?php
                    $sql="select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
                    $db_lng_mst=$obj->MySQLSelect($sql);
                    $count_lang = count($db_lng_mst);
					if($count_lang > 1){
		?>
		
        <div class="footer-box1">
            
           
            
            <div class="lang" id="lang_open">
                <b>
                    <a href="javascript:void(0);"><?=$langage_lbl['LBL_LANGUAGE_SELECT']; ?></a>
                </b>
            </div>
            <div class="lang-all" id="lang_box">
                <ul>
                    <?php 
                    foreach ($db_lng_mst as $key => $value) { 
                        $status_lang = "";
                        if($_SESSION['sess_lang']==$value['vCode']) {
                            $status_lang = "active";
                        } ?>
                        <li onclick="change_lang(this.id);" id="<?php echo $value['vCode']; ?>"><a href="javascript:void(0);" class="<?php echo $status_lang; ?>"><?php echo ucfirst(strtolower($value['vTitle'])); ?></a></li>
                    <?php } 
                    if($count_lang > 4) { ?>
                    <!--     <li><a href="contact-us" ><?=$langage_lbl['LBL_LANG_NOT_FIND']; ?></a></li> -->
                    <?php } ?>
                </ul>
            </div>
        </div>
        <?php
					}
 
     
		if((!empty($FB_LINK_FOOTER))  || (!empty($TWITTER_LINK_FOOTER)) || (!empty($LINKEDIN_LINK_FOOTER)) || (!empty($GOOGLE_LINK_FOOTER)) || (!empty($INSTAGRAM_LINK_FOOTER))){?>
         <div class="app_store desktop">
                    <a  class="myBtn" ><img src="assets/img/home-new/ios.png" ></a>
                    <a  class="myBtn" ><img src="assets/img/home-new/googleplay.png
    " ></a>
         </div>
         
          <div class="app_store mobile">
                    <a  href="#" ><img src="assets/img/home-new/ios.png" ></a>
                    <a  href="#" ><img src="assets/img/home-new/googleplay.png
    " ></a>
         </div>
         
          <b>
            <?php if(!empty($FB_LINK_FOOTER)){ ?>
            <a rel="nofollow" target="_blank" href="http://hurleys.anviam.in:8086/coming-soon.php"><img alt="" src="assets/img/home-new/fb.jpg" onclick="return submitsearch(document.frmsearch);" onmouseover="this.src='assets/img/home-new/fb-hover.jpg'" onmouseout="this.src='assets/img/home-new/fb.jpg'"></a>
            <?php } 
            if(!empty($TWITTER_LINK_FOOTER)){ ?>
            <a rel="nofollow" target="_blank" href="http://hurleys.anviam.in:8086/coming-soon.php"><img alt="" src="assets/img/home-new/twitter.jpg" onclick="return submitsearch(document.frmsearch);" onmouseover="this.src='assets/img/home-new/twitter-hover.jpg'" onmouseout="this.src='assets/img/home-new/twitter.jpg'"></a>
            <?php } if(!empty($LINKEDIN_LINK_FOOTER)){  ?>
           <!-- <a rel="nofollow" target="_blank" href="<?php// echo $LINKEDIN_LINK_FOOTER;?>"><img alt="" src="assets/img/home-new/linkedin.jpg" onclick="return submitsearch(document.frmsearch);" onmouseover="this.src='assets/img/home-new/linkedin-hover.jpg'" onmouseout="this.src='assets/img/home-new/linkedin.jpg'"></a>-->
            <?php } ?>
            <!--<a rel="nofollow" target="_blank" href="#"><img alt="" src="assets/img/home-new/pinterest.jpg" onclick="return submitsearch(document.frmsearch);" onmouseover="this.src='assets/img/home-new/pinterest-hover.jpg'" onmouseout="this.src='assets/img/home-new/pinterest.jpg'"></a>-->
          </b>
        <?php } ?>
    </div>
    <!-- -->
    <div class="footer-bottom-part">
      <p>&copy; <?= $COPYRIGHT_TEXT ?><!--  <a href="#">Food on Demand</a> --></p>
    </div>
    <div style="clear:both;"></div>
  </div>
</div>

<!-- <div class="footer">
  <div class="footer-top-part">
        <div class="footer-inner">
            <div class="footer-box1">
                <div class="lang" id="lang_open">
                <b><a href="javascript:void(0);"><?=$langage_lbl['LBL_LANGUAGE_SELECT']; ?></a></b>
                </div>
                <div class="lang-all" id="lang_box">
                    <ul>
                    <?php
                    $sql="select vTitle, vCode, vCurrencyCode, eDefault from language_master where eStatus='Active' ORDER BY iDispOrder ASC";
                    $db_lng_mst=$obj->MySQLSelect($sql);
                    $count_lang = count($db_lng_mst);
                    foreach ($db_lng_mst as $key => $value) { 
                        $status_lang = "";
                        if($_SESSION['sess_lang']==$value['vCode']) {
                            $status_lang = "active";
                        } ?>
                    <li onclick="change_lang(this.id);" id="<?php echo $value['vCode']; ?>"><a href="javascript:void(0);" class="<?php echo $status_lang; ?>"><?php echo ucfirst(strtolower($value['vTitle'])); ?></a></li>
                    <?php } 
                    if($count_lang > 4) { ?>

                    <li><a href="contact-us" ><?=$langage_lbl['LBL_LANG_NOT_FIND']; ?></a></li>
                    <?php } ?>
                    </ul>
                    </div>
					<?php if((!empty($FB_LINK_FOOTER))  || (!empty($TWITTER_LINK_FOOTER)) || (!empty($LINKEDIN_LINK_FOOTER)) || (!empty($GOOGLE_LINK_FOOTER)) || (!empty($INSTAGRAM_LINK_FOOTER))){?>
                    <span>
					<?php if(!empty($FB_LINK_FOOTER)){ ?>
                    <a href="<?php echo $FB_LINK_FOOTER;?>" target="_blank"><i class="fa fa-facebook"></i></a> 
					<?php } 
					if(!empty($TWITTER_LINK_FOOTER)){ ?>
                    <a href="<?php echo $TWITTER_LINK_FOOTER;?>" target="_blank"><i class="fa fa-twitter"></i></a>
						<?php } 
					if(!empty($LINKEDIN_LINK_FOOTER)){ ?>
                    <a href="<?php echo $LINKEDIN_LINK_FOOTER;?>" target="_blank"><i class="fa fa-linkedin"></i></a>
					<?php } 
					if(!empty($GOOGLE_LINK_FOOTER)){ ?>
                    <a href="<?php echo $GOOGLE_LINK_FOOTER;?>" target="_blank"><i class="fa fa-google"></i></a>
					<?php } 
					if(!empty($INSTAGRAM_LINK_FOOTER)){ ?>
                    <a href="<?php echo $INSTAGRAM_LINK_FOOTER;?>" target="_blank"><i class="fa fa-instagram"></i></a>
					<?php } ?>
                    </span>
					<?php } ?>
                    
            </div>
            <div class="footer-box2">
                <ul>
                    <li><a href="how-it-works"><?=$langage_lbl['LBL_HOW_IT_WORKS']; ?></a></li>
                    <li><a href="trust-safty-insurance"><?=$langage_lbl['LBL_SAFETY_AND_INSURANCE']; ?></a></li>
                    <li><a href="terms-condition"><?=$langage_lbl['LBL_FOOTER_TERMS_AND_CONDITION']; ?></a></li>
					<li><a href="faq"><?=$langage_lbl['LBL_FAQs']; ?></a></li>
                    <li><a href="privacy-policy"><?=$langage_lbl['LBL_PRIVACY_POLICY_TEXT']; ?></a></li>
                </ul>
                <ul>
                    <li><a href="about"><?=$langage_lbl['LBL_ABOUT_US_HEADER_TXT']; ?></a></li>
                    <li><a href="contact-us"><?=$langage_lbl['LBL_FOOTER_HOME_CONTACT_US_TXT']; ?></a></li>
                    <li><a href="help-center"><?=$langage_lbl['LBL_FOOTER_HOME_HELP_CENTER']; ?></a></li>
                    <li><a href="legal"><?=$langage_lbl['LBL_LEGAL']; ?></a></li>
                </ul>
            </div>
            <div class="footer-box3"> 
                <span>
                    <a href="<?=$IPHONE_APP_LINK?>" target="_blank"><img src="assets/img/app-stor-img.png" alt=""></a>
                </span> 
                <span>
                    <a href="<?=$ANDROID_APP_LINK?>" target="_blank"><img src="assets/img/google-play-img.png" alt=""></a>
                </span> 
            </div>
            <div style="clear:both;"></div>
            </div>
        </div>
        <div class="footer-bottom-part"> 
                <div class="footer-inner">
            <span>&copy; <?= $COPYRIGHT_TEXT ?></span>
        </div>
        <div style=" clear:both;"></div>
    </div>
</div> -->
<script>
function change_lang(lang){
    document.location='common.php?lang='+lang;
}
</script>


<script type="text/javascript">
    $(document).ready(function(){
        $(".custom-select-new1").each(function(){
            var selectedOption = $(this).find(":selected").text();
            $(this).wrap("<em class='select-wrapper'></em>");
            $(this).after("<em class='holder'>"+selectedOption+"</em>");
        });
        $(".custom-select-new1").change(function(){
            var selectedOption = $(this).find(":selected").text();
            $(this).next(".holder").text(selectedOption);
        });
		$("#lang_box").hide();
		$("#lang_open").click(function(){
			$("#lang_box").slideToggle();
		});

        $('html').click(function(e) {
            $('#lang_box').hide(); 
        });
            
        $('#lang_open').click(function(e){
            e.stopPropagation();
        });

    })
</script>


<!--model popup box script-->
<!-- The Modal -->
        <div id="myModal" class="modal">
        
          <!-- Modal content -->
          <div class="modal-content">
            <span class="close">&times;</span>
            <form method="post">
                <label>Phone Number:</label>
                <div class="phone_number">
                     
                    <input type="tel" class="phone_no" id="txtnumber" maxlength="12" name="phone_no" placeholder="xxx-xxx-xxxx" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" required />
                    <span class="validity"></span>
                </div>
                <input type="submit" name="phone_submit" class="phone_submit" value="Text me the app" />
                
                <p>By entering your mobile number you agree to receive a download link, which may be sent by an autodialer. Text and
data rates may apply.</p>
                
            </form>
          </div>
        
        </div>
        
        
         <script>
             $(function () {
            
                        $('#txtnumber').keydown(function (e) { 
                         var key = e.charCode || e.keyCode || 0;
                         $text = $(this); 
                         if (key !== 8 && key !== 9) {
                             if ($text.val().length === 3) {
                                 $text.val($text.val() + '-');
                             }
                             if ($text.val().length === 7) {
                                 $text.val($text.val() + '-');
                             }
            
                         }
            
                         return (key == 8 || key == 9 || key == 46 || (key >= 48 && key <= 57) || (key >= 96 && key <= 105));
                     })
            });
        </script>

 <script>
    
            // Get the modal
            var modal = document.getElementById('myModal');
            
            // Get the button that opens the modal
            var btn = document.getElementsByClassName("myBtn")[0];
            
             
            
            // Get the <span> element that closes the modal
            var span = document.getElementsByClassName("close")[0];
            
            // When the user clicks the button, open the modal 
            btn.onclick = function() {
                modal.style.display = "block";
            }
            
            // When the user clicks on <span> (x), close the modal
            span.onclick = function() {
                modal.style.display = "none";
            }
            
            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            
            
            var mybtn =  document.getElementsByClassName("myBtn");
            
            for(i=0; i<=mybtn.length; i++){
            	mybtn[i].onclick = function() {
                    modal.style.display = "block";
                }
            
            }
            </script>
            
