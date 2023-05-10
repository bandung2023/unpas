<?php
include  'db.php';
include  'function.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_GET['pid'])) { $pid=$_GET['pid']; } else { $pid = ''; }
// First Checking PID
if(empty($pid)) {
 echo "No Selected PID";    
 }else {
       echo "Grab PID  => ".$pid."<br> ";
        //Get Cron Panel
        $cp = $mysqli->query("SELECT * FROM cronpanel WHERE cronpid='$pid' AND status='active'");
        if($cp === false) {
         echo "Cron Panel Error :";
        }else{
        $rp=$cp->fetch_array();
        $niche=$rp['niche'];
        $cpdesc=$rp['cdesc'];
        $blogact=$rp['act'];
        $virtual=$rp['vt'];
        }
        //Get Cron
        $cn = $mysqli->query("SELECT * FROM cdesc WHERE id='$cpdesc'");
        if($cn === false) 
        {
        echo "Desc Error :";
        }else{
        $cr=$cn->fetch_array();
        $ctitle=$cr['ctitle'];
        $cbody=$cr['cbody'];
        }
        $xt = $mysqli->query("SELECT * FROM xtracontent WHERE niche='$niche' ORDER BY RAND()");
        if($xt === false) 
        {
        echo "Xtra Error :";
        }else{
        $xc=$xt->fetch_array();
        $contentspin =$xc['cspin'];
        $cspin=Spin($contentspin);
        }
        // Blog Account
        $ba = $mysqli->query("SELECT * FROM account WHERE id='$blogact'");
        if($ba === false) {
        echo "Account Error :";
        }else{
        $rb=$ba->fetch_array();
        $secmail=$rb['secmail'];
        $uname=$rb['uname'];
        $upswd=$rb['upswd'];
        $sendname=$rb['sendname'];
        $receipname=$rb['receipname'];
        $blogurl=$rb['blogurl'];
        $blogredir=$rb['blogredir'];
        $img1=$rb['img1'];
        $img2=$rb['img2'];
        $img3=$rb['img3'];
        $img4=$rb['img4'];
        $img5=$rb['img5'];
        }
        // TMDB
        $dtm = $mysqli->query("SELECT * FROM dbtmdb WHERE niche='$niche' AND $virtual !='Y' ORDER BY RAND()");
        if($dtm === false) {
        echo "TMDB Error :";
        }else{
        $az=$dtm->fetch_array();
        $dbtmdb=$az['tmdb'];
        $idb=$az['id'];
        $tmdb = preg_replace('/\s+/','',$dbtmdb);
        }
      
         $nch = $mysqli->query("SELECT * FROM webservice WHERE id='$niche'");
        if($nch === false) {
         echo "Niche Error :";
        }else{
        $nh=$nch->fetch_array();
        $jservice =$nh['jservice'];
        $tmdbragex=$nh['ccd'];
        $weblp =$nh['weblp'];
        $htacs=$nh['htacs'];
        }
       
        $tkwd1 = $mysqli->query("SELECT * FROM kwdtitle WHERE kniche='$niche' AND karea='kwd' ORDER BY RAND()");
        if($tkwd1 === false) {
         echo "TkWd 1 Error :";
        }else{
        $tk1=$tkwd1->fetch_array();
        $tkwd =$tk1['kwd'];
        }
        
        $tkwd2 = $mysqli->query("SELECT * FROM kwdtitle WHERE kniche='$niche' AND karea='rcta' ORDER BY RAND()");
        if($tkwd2 === false) {
         echo "TkWd 2 Error :";
        }else{
        $tk2=$tkwd2->fetch_array();
        $rcta =$tk2['kwd'];
        }
        
        $tkwd3 = $mysqli->query("SELECT * FROM kwdtitle WHERE kniche='$niche' AND karea='lcta' ORDER BY RAND()");
        if($tkwd3 === false) {
         echo "TkWd 3 Error :";
        }else{
        $tk3=$tkwd3->fetch_array();
        $lcta =$tk3['kwd'];
        }
        
        $tapi = $mysqli->query("SELECT * FROM tmdbapi ORDER BY RAND()");
        if($tapi === false) {
         echo "APi Error :";
        }else{
        $ap=$tapi->fetch_array();
        $tmdbapi=$ap['tmdbapi'];
        }
         $rrelated = $mysqli->query("SELECT * FROM related WHERE niche='$niche' ORDER BY RAND()");
        if($rrelated === false) {
        echo "Related DB Error :";
        }else{
        $az=$rrelated->fetch_array();
        $title1=$az['title1'];
        $title2=$az['title2'];
        $title3=$az['title3'];
        $title4=$az['title4'];
        $poster1=$az['poster1'];
        $poster2=$az['poster2'];
        $poster3=$az['poster3'];
        $poster4=$az['poster4'];
        $tm1=$az['tmdb1'];
        $tm2=$az['tmdb2'];
        $tm3=$az['tmdb3'];
        $tm4=$az['tmdb4'];
        $tmdb1 = preg_replace('/\s+/','',$tm1);
        $tmdb2 = preg_replace('/\s+/','',$tm2);
        $tmdb3 = preg_replace('/\s+/','',$tm3);
        $tmdb4 = preg_replace('/\s+/','',$tm4);
        }
       
       $conf = $mysqli->query("SELECT * FROM dbconf WHERE id='1'");
       if($conf === false) {
       echo "Conf Error :";
       }else{
       $cfg=$conf->fetch_array();
       $smode=$cfg['smode'];
       $linkmode=$cfg['linkmode'];
       $weblink = preg_replace('/\s+/','',$linkmode);
       }
       
   
    //Sending Process
    if(empty($tmdb)) {
    echo "No Selected TMDB";    
     }else {
    echo "Send TMDB => ".$tmdb."<br> ";
   
    $getdb =getMovie($jservice,$tmdb);
    if(empty($getdb)) { 
    }else {
    $tmdb         = $getdb['tmdb'];
    $imdb         = $getdb['imdb'];
    $title        = $getdb['mtitle'];
    $desc         = $getdb['mdesc'];
    $release      = $getdb['mrelease'];
    $runtime      = $getdb['mruntime'];
    $vavg         = $getdb['voteavg'];
    $vcount       = $getdb['votecount'];
    $genre        = $getdb['genre'];
    $year         = $getdb['myear'];
    $tagline      = $getdb['tagline'];
    $poster       = $getdb['tposter'];
    $backdrop     = $getdb['tbackdrop'];
    
    
 
    $urllink    ="".$blogredir."/".$tmdb."";
    $rln1       ="".$blogredir."/".$tmdb1."";
    $rln2       ="".$blogredir."/".$tmdb2."";
    $rln3       ="".$blogredir."/".$tmdb3."";
    $rln4       ="".$blogredir."/".$tmdb4."";
    
   
         
    $findstring = array("{title}","{release}","{desc}","{vavg}","{vcount}","{runtime}","{year}","{poster}","{backdrop}","{tagline}",
                        "{urllink}","{img1}","{img2}","{img3}","{img4}","{img5}","{tkwd}","{lcta}","{rcta}","{cspin}",
                        "{rt1}","{rtm1}","{rp1}","{rln1}","{rt2}","{rtm2}","{rp2}","{rln2}","{rt3}","{rtm3}","{rp3}","{rln3}","{rt4}","{rtm4}","{rp4}","{rln4}");

    $replace = array("$title","$release","$desc","$vavg","$vcount","$runtime","$year","$poster","$backdrop","$tagline",
                     "$urllink","$img1","$img2","$img3","$img4","$img5","".$tkwd."","".$lcta."","".$rcta."","$cspin",
                     "$title1","$tmdb1","$poster1","$rln1","$title2","$tmdb2","$poster2","$rln2","$title3","$tmdb3","$poster3","$rln3","$title4","$tmdb4","$poster4","$rln4");

    $subjectmail  = str_replace($findstring, $replace , $ctitle);
    $bodymail     = str_replace($findstring, $replace , $cbody);
   
    if ($smode == 'csend'){     
                require 'phpmailer/PHPMailerAutoload.php';
                $mail = new PHPMailer;

                $mail->Username = $uname;
                $mail->Password = $upswd;
                $mail->From = $uname;
                $mail->FromName = $sendname;
                // Email Sending Details
                $mail->isHTML(true);
                $mail->addAddress($secmail,$receipname);
                $mail->Subject = $subjectmail;
                $mail->msgHTML($bodymail);

                // Success or Failure
                if (!$mail->send()) {
                $error = "Mailer Error: " . $mail->ErrorInfo;
                echo '<p id="para">'.$error.'</p>';
                }else {
                   echo '<p><font color=blue>Message sent! Begin Proses Update</font></p>';
                
                  $sup ="UPDATE dbtmdb SET $virtual='Y' WHERE id='$idb'";
                   if ($mysqli->query($sup) === TRUE) { } else { }
                 
              }
 
          
 
            }elseif ($smode == 'cview'){ 
             echo "".$subjectmail." <br> ".$bodymail." ";
            
           }else {
    
             echo "No Mode Selected";
           }
        }   
    }
}

$mysqli->close();

