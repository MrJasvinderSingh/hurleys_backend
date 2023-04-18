<?php
error_reporting(0);
include_once('common.php');
include_once('generalFunctions.php');
if(function_exists($_GET['type'])) {
    $objolineorder = new onlineordering();
    
   $objolineorder->$_GET['type']();
}
class onlineordering
{
        
        function unformatnumber($phone)
        {
           //$format =   preg_replace('/[^A-Za-z0-9\-]/', '', $phone);
           $phonenumber = str_replace('-', '', $phone);
           return $phonenumber;
        }
        
        
//        public function check()
//        {
//            $this->layout = false;
//            $this->autoRender = false;
//            $value = '(987) 654-3210';
//             $phonenumber = $this->unformatnumber($value);
//             echo $phonenumber;
//        }
        
        public function checkexisting()
        {
           
            $response = array();
            if($this->request->is('ajax'))
            {
                
                $phonenumber = $this->unformatnumber($this->request->data['phone']);
                $customer = $this->RegisterUser->find('first', array('conditions'=>array('RegisterUser.vPhone'=>$phonenumber)));
                if(!empty($customer))
                {
                    $response['status'] = true;
                    $response['RegisterUserVEmail'] = $customer['RegisterUser']['vEmail'];
                    $response['autocomplete'] = $customer['RegisterUser']['tDestinationAddress'];
                    $response['RegisterUserVName'] = $customer['RegisterUser']['vName'];
                    $response['RegisterUserVLastName'] = $customer['RegisterUser']['vLastName'];
                    $response['iUserId'] = $customer['RegisterUser']['iUserId'];
                    $response['geolat'] = $customer['RegisterUser']['tDestinationLatitude'];
                    $response['geolong'] = $customer['RegisterUser']['tDestinationLongitude'];
                    
                }
                else {
                    $response['status'] = false;
                    $response['RegisterUserVEmail'] = '';
                    $response['autocomplete'] = '';
                    $response['RegisterUserVName'] = '';
                    $response['RegisterUserVLastName'] = '';
                    $response['iUserId'] = '0';
                }
            }
            return json_encode($response);
        }
        
	public function index()
        {
            $oldtxnid = $this->Session->read('txnid');
            if($oldtxnid)
            {
                 $carts = $this->Cart->find('all', array('conditions'=>array('Cart.txnid'=>$oldtxnid)));
                 if(!empty($carts))
                 {
          foreach ($carts as $cart):
              $this->Cart->id = $cart['Cart']['id'];
              $this->Cart->delete();
          endforeach;
         // $this->Cart->query("ALTER TABLE `carts` AUTO_INCREMENT = 1;");
                 }
            }
            
            $this->Session->write('txnid', 0);
            $this->Session->write('iUserAddressId', 0);
            $this->Session->write('iUserId', 0);
            $this->Session->write('iCompanyId', 0);
            
            
        }
        
        
         function get_lat_long($address) {

            $address = "a45, quark city, mohali, punjab, india";
   $array = array();
   $geo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyBpS6KkKA19pQD7Ai663AbGInvuSZWxICI&address='.urlencode($address).'&sensor=false');

   // We convert the JSON to an array
   $geo = json_decode($geo, true);

   // If everything is cool
   if ($geo['status'] = 'OK') {
      $latitude = $geo['results'][0]['geometry']['location']['lat'];
      $longitude = $geo['results'][0]['geometry']['location']['lng'];
      $array = array('lat'=> $latitude ,'lng'=>$longitude);
   }
   
   return $array;
}
        
        public function savecustomerdetails()
        {
            $iuserAddressid = 0;
            if($this->request->is('post', 'put') && !empty($this->request->data['RegisterUser']['vPhone']) && !empty($this->request->data['RegisterUser']['vName']))
            {
               
                if(empty($this->request->data['RegisterUser']['iUserId']))
                {
                $this->RegisterUser->create();
                $this->request->data['RegisterUser']['tRegistrationDate'] = date('Y-m-d H:i:s');
//                $this->request->data['RegisterUser']['tLastOnline'] = date('Y-m-d H:i:s');
//                 $this->request->data['RegisterUser']['iRefUserId'] = 0;
//                 $this->request->data['RegisterUser']['iHotelId'] = 0;
//                 $this->request->data['RegisterUser']['vPassword'] = 0;
//                 $this->request->data['RegisterUser']['vCountry'] = 'US';
//                 $this->request->data['RegisterUser']['vState'] = '0';
//                 $this->request->data['RegisterUser']['vCreditCard'] = 0;
//                 $this->request->data['RegisterUser']['vCountry'] = 'US';
//                 $this->RegisterUser->validate = false;
                
                 
                }
                else
                {
                    $this->RegisterUser->iUserId = $this->request->data['RegisterUser']['iUserId'];
                   // $this->Session->write('iUserId', $this->RegisterUser->iUserId);
                    $iuserAddress = $this->UserAddress->find('first', array('conditions'=>array('UserAddress.iUserId'=>$this->request->data['RegisterUser']['iUserId'])));
                    if(!empty($iuserAddress))
                    {
                        $iuserAddressid = $iuserAddress['UserAddress']['iUserAddressId'];
                        
                       
                        
                        $this->Session->write('iUserAddressId', $iuserAddressid);
                    }
                    
                }
                
               $this->request->data['RegisterUser']['vPhone'] = $this->unformatnumber($this->request->data['RegisterUser']['vPhone']);
                if(!empty($this->request->data['RegisterUser']['geolat']) && !empty($this->request->data['RegisterUser']['geolong']))
                {
                 $this->request->data['RegisterUser']['tDestinationLatitude'] = $this->request->data['RegisterUser']['geolat'];
                  $this->request->data['RegisterUser']['tDestinationLongitude'] = $this->request->data['RegisterUser']['geolong'];
                   $this->request->data['RegisterUser']['vLatitude'] = $this->request->data['RegisterUser']['geolat'];
                  $this->request->data['RegisterUser']['vLongitude'] = $this->request->data['RegisterUser']['geolong'];
                }
                else
                {
                    $addressresult = $this->get_lat_long($this->request->data['RegisterUser']['tDestinationAddress']);
                     $this->request->data['RegisterUser']['tDestinationLatitude'] =$addressresult['lat'];
                  $this->request->data['RegisterUser']['tDestinationLongitude'] = $addressresult['lng'];
                  $this->request->data['RegisterUser']['vLatitude'] = $addressresult['lat'];
                  $this->request->data['RegisterUser']['vLongitude'] = $addressresult['lng'];
                }
                
                
                if($this->RegisterUser->save($this->request->data))
                {
                    $user_id =  $this->RegisterUser->id;
                    $this->Session->write('txnid', uniqid());
                    
                    $this->Session->write('iUserId',$user_id);
                  //  echo $iuserAddressid; exit;
                    if($iuserAddressid == 0)
                    {
                   $useraddress['UserAddress'][] = array(
                        'iUserId'=>$user_id,
                       'eUserType'=>'Rider',
                       'vServiceAddress'=>$this->request->data['RegisterUser']['tDestinationAddress'],
                       'vBuildingNo'=>'',
                       'vLandmark'=>'',
                       'vAddressType'=>'',
                       'vLatitude '=>$this->request->data['RegisterUser']['tDestinationLatitude'],
                       'vLongitude'=>$this->request->data['RegisterUser']['tDestinationLongitude'],
                       'dAddedDate'=>date('Y-m-d H:i:s'),
                       'vTimeZone '=>'America/New_York',
                       'eStatus'=>'Active'
                    );
                   if($this->UserAddress->saveAll($useraddress['UserAddress']))
                   {
                        $this->Session->write('iUserAddressId', $this->UserAddress->id);
                   }
                    }
                    else {
                             $this->UserAddress->iUserAddressId = $iuserAddressid;
                        $this->UserAddress->saveField('vServiceAddress', $this->request->data['RegisterUser']['tDestinationAddress']);
                        $this->UserAddress->saveField('vLatitude', $this->request->data['RegisterUser']['tDestinationLatitude']);
                        $this->UserAddress->saveField('vLongitude', $this->request->data['RegisterUser']['tDestinationLongitude']);
                    }
//                    }
                  //$this->Flash->success(__('The customer has been saved.'));
                  //?geolat='.$this->request->data['RegisterUser']['geolat'].'&geolong='.$this->request->data['RegisterUser']['geolong']
                   // echo 'restaurants?geolat='.$this->request->data['RegisterUser']['geolat'].'&geolong='.$this->request->data['RegisterUser']['geolong']; exit;
                    
				return $this->redirect(array('action' => 'restaurants',$this->request->data['RegisterUser']['geolat'],$this->request->data['RegisterUser']['geolong']));
			} else {
				echo 'The customer could not be saved. Please, try again.';
			}
            }
            else
            {
                echo '<div class="col-md-12 btn btn-danger">Please complete all the fields and, try again.</div>';
            }
        }
        
        function distance($lat1, $lon1, $lat2, $lon2, $unit = null) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}
        public function restaurants($geolaturl =null , $geolongurl = null)
        {
            $this->layout = false;
            $this->autoRender = true;
            $geolat = '';
            $geolong = '';
            if($this->request->is('ajax', 'post') && !empty($this->request->data['Search']))
            {
                 $data_company  = $this->Company->find('all', array('conditions'=>array('Company.eStatus'=>'Active','Company.eAvailable'=>'Yes', 'Company.vCompany LIKE'=>'%'.$this->request->data['Search']['restaurant'].'%')));
                 //,'Company.vCity'=>'36'
                  $geolat = $this->request->data['Search']['geolat'];
            $geolong = $this->request->data['Search']['geolong'];
            }
            else
            {
                 $data_company  = $this->Company->find('all', array('conditions'=>array('Company.eStatus'=>'Active','Company.eAvailable'=>'Yes')));
                 //,'Company.vCity'=>'36'
                  $geolat = $geolaturl;
            $geolong = $geolongurl;
            }
           
            $companies = array();
            foreach ($data_company as $companydata)
            {
                 $distancedata = '';
    
     $distancedata = $this->distance($geolat, $geolong, $companydata['Company']['vRestuarantLocationLat'], $companydata['Company']['vRestuarantLocationLong']);
                if($distancedata < 20) 
     {
    $companies[] = array(
        'iCompanyId'=>$companydata['Company']['iCompanyId'],
        'vCompany'=>$companydata['Company']['vCompany'],
        'vImage'=>$companydata['Company']['vImage'] ?  'http://hurleys.anviam.in:8086/webimages/upload/Company'.DS.$companydata['Company']['iCompanyId'].DS.$companydata['Company']['vImage'] : 'http://hurleys.anviam.in:8086/assets/img/logo.png',
        'minorder'=>$companydata['Company']['fMinOrderValue'],
        'rating'=>$companydata['Company']['vAvgRating'],
        'distance'=> $distancedata
    );
     }
            }
            if(!empty($companies)) {
array_multisort( array_column($companies, "distance"), SORT_ASC, $companies );
}
            
            
                       
            $this->set(compact('companies'));

        }
        
        public function getsearchlist()
          {
              $this->layout = false;
              $this->autoRender = true;
               $geolat = $this->request->data['lat'];
            $geolong = $this->request->data['long'];
            $companies = array();
              if($this->request->is('post', 'put') && !empty($this->request->data['keyword']))
              {
                   $this->Company->recursive = 0;
                   //,'Company.vCity'=>'36'
                    $data_company =  $this->Company->find('all', array('conditions' => array('Company.eStatus'=>'Active','Company.eAvailable'=>'Yes', 'Company.vCompany LIKE'=>'%'.$this->request->data['keyword'].'%'), 'limit' => 10, 'fields'=>array('iCompanyId', 'vCompany','vRestuarantLocationLat','vRestuarantLocationLong')));
              
             
             
          
            $companies = array();
            foreach ($data_company as $companydata)
            {
                 $distancedata = '';
    
     $distancedata = $this->distance($geolat, $geolong, $companydata['Company']['vRestuarantLocationLat'], $companydata['Company']['vRestuarantLocationLong']);
                if($distancedata < 20) 
     {
    $companies[] = array(
        'iCompanyId'=>$companydata['Company']['iCompanyId'],
        'vCompany'=>$companydata['Company']['vCompany'],
         'distance'=> $distancedata
    );
     }
            }
            if(!empty($companies)) {
array_multisort( array_column($companies, "distance"), SORT_ASC, $companies );
}
            
            
              }
            $this->set(compact('companies'));
          }
        
        public function getmenucategory()
        {
           $this->layout = false;
           $this->autoRender = true;
           if($this->request->is('ajax'))
            {
            $categories  = $this->FoodMenu->find('all', array('conditions'=>array('FoodMenu.eStatus'=>'Active','FoodMenu.iCompanyId'=>$this->request->data['iCompanyId'])));
             $this->Session->write('iCompanyId',$this->request->data['iCompanyId']);
            
            $this->set(compact('categories'));
            }
           
        }
        
        
        public function getmenuitems()
        {
            $this->layout = false;
            $this->autoRender = true;
            if($this->request->is('ajax'))
            {
                
                $response = array();
                $menuitems = $this->MenuItem->find('all', array('conditions'=>array('MenuItem.eStatus'=>'Active','MenuItem.iFoodMenuId'=>$this->request->data['iFoodMenuId'])));
                
                if(!empty($menuitems)) {
                    foreach ($menuitems as $menuitem):

                    $menuoptions = $this->MenuitemOption->find('all', array('conditions'=>array('MenuitemOption.eStatus'=>'Active','MenuitemOption.iMenuItemId'=>$menuitem['MenuItem']['iMenuItemId'], 'MenuitemOption.eOptionType'=>'Options')));
                    $menuaddons = $this->MenuitemOption->find('all', array('conditions'=>array('MenuitemOption.eStatus'=>'Active','MenuitemOption.iMenuItemId'=>$menuitem['MenuItem']['iMenuItemId'], 'MenuitemOption.eOptionType'=>'Addon')));
                    
                     $response[] = array(
                            'menuitem'=>$menuitem['MenuItem'],
                            'menuoptions'=>$menuoptions ? $menuoptions : '',
                            'menuaddons'=>$menuaddons ? $menuaddons : ''
                        );
                
                    
                    endforeach;
                }
                else {
                     $response[] = array(
                        'menuitem'=>'',
                        'menuoptions'=>'',
                        'menuaddons'=>''
                      );
                }
              $this->set(compact('response'));
                
            }
        }
        
}

?>
