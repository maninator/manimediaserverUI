<?php
    /**
    * ApiController Class
    *
    * @package Mani Media Manager
    * @author maninator
    * @copyright 2016
    * @version $Id: ApiController.class.php, v1.00 2018-01-23 18:20:24 gewa Exp $
    */

    if (!defined("_MANI"))
        die('Direct access to this location is not allowed.');


class ApiController
{
     
    /**
    * ApiController::index()
    * 
    * @return
    */
    public function index()
    { 
        echo "api access not allow";
        exit;
    }

    /**
    * ApiController::checkPlan()
    * 
    * @return
    */
    public function checkPlan()
    {     
        //$mTable = "memberships";
         
        if(isset($_POST['planid']) && !empty($_POST['planid'])) {
       
            $row = Db::run()->first(Membership::mTable, null, array("id" => $_POST['planid']));
             

            if(count($row) < 1) {
                
                echo json_encode(['status' => false,'message' => "Invalid membership plan"]);
                exit;
            }

            $expiry = date('d/m/Y',strtotime('+'.$row->days.' years'));
            $data = ['planid' => $row->id,'status' => true,'amount' => $row->price,'title' => $row->title,'description' => $row->description,'expiry' => $expiry];
         
            echo json_encode($data);
            exit;

        } else {
            
            echo json_encode(['status' => false,'message' => "Required fields are empty"]);
            exit;
        }          
    }


    /**
    * ApiController::RegistrationFromApi()
    * 
    * @return
    */
    public function RegistrationFromApi()
    {
        //stripeClientId
        $rules = array(
            'fname' => array('required|string|min_len,3|max_len,60', Lang::$word->M_FNAME),
            'lname' => array('required|string|min_len,3|max_len,60', Lang::$word->M_LNAME),
            'password' => array('required|string|min_len,6|max_len,20', Lang::$word->M_PASSWORD),
            'email' => array('required|email', Lang::$word->M_EMAIL),
            'tnxid' => array('required', Lang::$word->M_TNX),
            'amount' => array('required', Lang::$word->M_AMOUNT),
            'planid' => array('required', Lang::$word->M_PLAN),
           // 'captcha' => array('required|numeric|exact_len,5', Lang::$word->CAPTCHA),
        );
   
        if(App::Core()->enable_tax) {
            //$rules['address'] = array('required|string|min_len,3|max_len,80', Lang::$word->M_ADDRESS);
            $rules['city'] = array('required|string|min_len,2|max_len,80', Lang::$word->M_CITY);
            $rules['zip'] = array('required|string|min_len,3|max_len,30', Lang::$word->M_ZIP);
            $rules['state'] = array('required|string|min_len,2|max_len,80', Lang::$word->M_STATE);
            $rules['country'] = array('required|string|exact_len,2', Lang::$word->M_COUNTRY);
        }

        // check member ship plan

        $row = Db::run()->first(Membership::mTable, null, array("id" => $_POST['planid']));

        if(count($row) < 1) {

            echo json_encode(['status' => false,'message' => "Invalid membership plan"]);
            exit;
        }


        $validate = Validator::instance();
        $safe = $validate->doValidate($_POST, $rules);
          
        $name = $safe->fname." ".$safe->lname;
        $email = $safe->email;

           
        if (!empty($safe->email)) {

            if (Auth::emailExists($safe->email)){
                  
                echo json_encode(['status' => false,'message' => Lang::$word->M_EMAIL_R2]);
                exit;
            }
        }

        Content::verifyCustomFields();
          
        if (empty(Message::$msgs)) {
            $salt = '';
            $hash = App::Auth()->create_hash(Validator::cleanOut($_POST['password']), $salt);
            $username = Utility::randomString();
            $core = App::Core();

            if ($core->reg_verify == 1) {
                $active = "t";
            } elseif ($core->auto_verify == 0) {
                $active = "n";
            } else {
                $active = "y";
            }
            
            $data = array(
                'username' => $username,
                'email' => $safe->email,
                'lname' => $safe->lname,
                'fname' => $safe->fname,
                'hash' => $hash,
                'salt' => $salt,
                'type' => "member",
                'token' => Utility::randNumbers(),
                'active' => $active,
                'userlevel' => 1,
              );
              
            if(App::Core()->enable_tax) {
                $data['address'] = $safe->address;
                $data['city'] = $safe->city;
                $data['state'] = $safe->state;
                $data['zip'] = $safe->zip;
                $data['country'] = $safe->country;
            }
            // Start Custom Fields
            $fl_array = Utility::array_key_exists_wildcard($_POST, 'custom_*', 'key-value');
            if ($fl_array) {
                $result = array();
                foreach ($fl_array as $val) {
                    array_push($result, Validator::sanitize($val));
                }
                $data['custom_fields'] = implode("::", array_filter($result));
            }
            $memberId = Db::run()->insert(Users::mTable, $data)->getLastInsertId();;
            
            if ($core->reg_verify == 1) {
                $message = Lang::$word->M_INFO7;
              
                $mailer = Mailer::sendMail();
                $tpl = Db::run()->first(Content::eTable, array("body", "subject"), array('typeid' => 'regMail'));
                $body = str_replace(array(
                    '[LOGO]',
                    '[DATE]',
                    '[COMPANY]',
                    '[USERNAME]',
                    '[EMAIL]',
                    '[PASSWORD]',
                    '[LINK]',
                    '[FB]',
                    '[TW]',
                    '[SITEURL]'), array(
                        Utility::getLogo(),
                        date('Y'),
                        $core->company,
                        $username,
                        $safe->email,
                        $safe->password,
                        Url::url("/activation", '?token=' . $data['token'] . '&email=' . $data['email']),
                        $core->social->facebook,
                        $core->social->twitter,
                        SITEURL
                    ), 
                    $tpl->body
                );
        
                $msg = Swift_Message::newInstance()
                    ->setSubject($tpl->subject)
                    ->setTo(array($data['email'] => $data['fname'] . ' ' . $data['lname']))
                    ->setFrom(array($core->site_email => $core->company))
                    ->setBody($body, 'text/html'
                );
                $mailer->send($msg);
              
            } elseif ($core->auto_verify == 0) {
                $message = Lang::$word->M_INFO7;
              
                $mailer = Mailer::sendMail();
                $tpl = Db::run()->first(Content::eTable, array("body", "subject"), array('typeid' => 'regMailPending'));
                $body = str_replace(array(
                    '[LOGO]',
                    '[DATE]',
                    '[COMPANY]',
                    '[USERNAME]',
                    '[EMAIL]',
                    '[PASSWORD]',
                    '[FB]',
                    '[TW]',
                    '[SITEURL]'), array(
                        Utility::getLogo(),
                        date('Y'),
                        $core->company,
                        $username,
                        $safe->email,
                        $safe->password,
                        $core->social->facebook,
                        $core->social->twitter,
                        SITEURL
                    ), 
                    $tpl->body
                );
        
                $msg = Swift_Message::newInstance()
                    ->setSubject($tpl->subject)
                    ->setTo(array($data['email'] => $data['fname'] . ' ' . $data['lname']))
                    ->setFrom(array($core->site_email => $core->company))
                    ->setBody($body, 'text/html'
                );
                $mailer->send($msg);
            } else {
                //login user
                App::Auth()->login($safe->email, $safe->password);
                $message = Lang::$word->M_INFO8;
              
                $mailer = Mailer::sendMail();
                $tpl = Db::run()->first(Content::eTable, array("body", "subject"), array('typeid' => 'welcomeEmail'));
                $body = str_replace(array(
                    '[LOGO]',
                    '[DATE]',
                    '[LINK]',
                    '[COMPANY]',
                    '[USERNAME]',
                    '[EMAIL]',
                    '[PASSWORD]',
                    '[FB]',
                    '[TW]',
                    '[SITEURL]'), array(
                        Utility::getLogo(),
                        date('Y'),
                        Url::url(""),
                        $core->company,
                        $username,
                        $safe->email,
                        $safe->password,
                        $core->social->facebook,
                        $core->social->twitter,
                        SITEURL
                    ), 
                    $tpl->body
                );
        
                $msg = Swift_Message::newInstance()
                    ->setSubject($tpl->subject)
                    ->setTo(array($data['email'] => $data['fname'] . ' ' . $data['lname']))
                    ->setFrom(array($core->site_email => $core->company))
                    ->setBody($body, 'text/html'
                );
                $mailer->send($msg);
            }
            
            if ($core->notify_admin) {
                $mailer = Mailer::sendMail();
                $tpl = Db::run()->first(Content::eTable, array("body", "subject"), array('typeid' => 'notifyAdmin'));
                $body = str_replace(array(
                    '[LOGO]',
                    '[DATE]',
                    '[EMAIL]',
                    '[COMPANY]',
                    '[USERNAME]',
                    '[NAME]',
                    '[IP]',
                    '[FB]',
                    '[TW]',
                    '[SITEURL]'), array(
                        Utility::getLogo(),
                        date('Y'),
                        $safe->email,
                        $core->company,
                        $username,
                        $data['fname'] . ' ' . $data['lname'],
                        Url::getIP(),
                        $core->social->facebook,
                        $core->social->twitter,
                        SITEURL
                    ), 
                    $tpl->body
                );
        
                $msg = Swift_Message::newInstance()
                    ->setSubject($tpl->subject)
                    ->setTo(array($core->site_email => $core->company))
                    ->setFrom(array($core->site_email => $core->company))
                    ->setBody($body, 'text/html'
                );
                $mailer->send($msg);
            }
              

            // insert in payment table, user_memberships, update member table
             
            // insert payemnt record
            $data = array(
                'txn_id' => $_POST['tnxid'],
                'membership_id' => $row->id,
                'user_id' => $memberId,
                'rate_amount' => $_POST['amount'],
                // 'coupon' => 0.00, //$_POST['coupon']
                'total' => $_POST['amount'], // $_POST['total']
                // 'tax' => 0, //$_POST['totaltax']
                'currency' => $_POST['currency'],
                'ip' => Url::getIP(),
                'pp' => "Stripe",
                'status' => 1,
            );
            $last_id = Db::run()->insert(Membership::pTable, $data)->getLastInsertId();
            //insert user membership
            $udata = array(
                'tid' => $last_id,
                'uid' => $memberId,
                'mid' => $row->id,
                'expire' => Membership::calculateDays($row->id),
                'recurring' => $row->recurring,
                'active' => 1,
            );

            //update user record
            $xdata = array(
                'stripe_cus' => $_POST['stripeClientId'],
                'membership_id' => $row->id,
                'mem_expire' => $udata['expire'],
            );
      
            Db::run()->insert(Membership::umTable, $udata);
            Db::run()->update(Users::mTable, $xdata, array("id" => $memberId));

            $jn['type'] = 'success';
            $jn['title'] = Lang::$word->SUCCESS;
            $jn['message'] = Lang::$word->STR_POK;

            /* == Notify Administrator == */
            $tpl = Db::run()->first(Content::eTable, array("body", "subject"), array('typeid' => 'payComplete'));
            $core = App::Core();
            $body = str_replace(array(
                '[LOGO]',
                '[COMPANY]',
                '[DATE]',
                '[SITEURL]',
                '[NAME]',
                '[ITEMNAME]',
                '[PRICE]',
                '[STATUS]',
                '[PP]',
                '[IP]',
                '[FB]',
                '[TW]'), array(
                    Utility::getLogo(),
                    $core->company,
                    date('Y'),
                    SITEURL,
                    $name,
                    $row->title,
                    $data['total'],
                    "Completed",
                    "Stripe",
                    Url::getIP(),
                    $core->social->facebook,
                    $core->social->twitter
                ), 
                $tpl->body
            );

            $msg = Swift_Message::newInstance()
                ->setSubject($tpl->subject)
                ->setTo(array($core->psite_email ? $core->psite_email : $core->site_email => $core->company))
                ->setFrom(array($email => $name))
                ->setBody($body, 'text/html');
            $mailer->send($msg);

            // $json['status'] => true,
            //  $json['type'] = 'success';
            // $json['title'] = Lang::$word->SUCCESS;
            // $json['redirect'] = SITEURL;
            // $json['message'] = $message;
            echo json_encode(['status' => 1]);
            exit;

            //   if (Db::run()->affected() && $mailer) {
            //   $json['status'] => true,
            // //  $json['type'] = 'success';
            //  // $json['title'] = Lang::$word->SUCCESS;
            //  // $json['redirect'] = SITEURL;
            //  // $json['message'] = $message;
            //   echo json_encode($json);
            //   exit;
            // } else {
            //   $json['status'] = false;
            //   //$json['type'] = 'error';
            //  // $json['title'] = Lang::$word->ERROR;
            //   $json['message'] = Lang::$word->M_INFO11;
            //   echo json_encode($json);
            //   exit;
            // }
              
        } else {
            Message::msgSingleStatus();
        }
    }
}