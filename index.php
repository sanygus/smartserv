<?php
$fdir = "/tmp/video";

function btst($vpost)
{
  if($vpost==$_POST['act']){echo(" style=\"font-weight:bold;\"");};
  
  switch($_POST['act'])
  {
    case 'rec':
      if(($vpost=='cast')or($vpost=='photo')){echo(" disabled");};
      break;
    case 'cast':
      if(($vpost=='rec')or($vpost=='photo')){echo(" disabled");};
      break;
    case 'off':
      echo(" disabled");
      break;
  }
}

$datatoserv = "null";//command
if($_POST['act']=='rec')
{
  if(!($_POST['ref']=='true')){$datatoserv = 'rec';};
  $mess .= "recording...";
}
elseif($_POST['act']=='cast')
{
  /*if(!($_POST['ref']=='true')){$log .= shell_exec('/home/pi/h264_v4l2_rtspserver/h264_v4l2_rtspserver > /dev/null 2>&1 &');};
  $mess .= "stream: rtsp://solarcomp.cloudapp.net:89/unicast";*/
}
elseif($_POST['act']=='photo')
{
  if(!($_POST['ref']=='true')){$datatoserv = 'photo';};
  $mess .= "Photo OK";
}
elseif($_POST['act']=='stop')
{
  if(!($_POST['ref']=='true')){$datatoserv = 'stop';};
  $mess .= "stop";
}
elseif($_POST['act']=='off')
{
  if(!($_POST['ref']=='true')){$datatoserv = 'off';};
  $mess .= "bye";
}
elseif(!($_POST['delfile']==''))
{
  unlink($fdir."/".$_POST['delfile']);//NOT SECURE!
  $mess .= "File '".$_POST['delfile']."' deleted";
};

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_connect($socket, "127.0.0.1", 2346);
if(!$socket){die("Ошибка подключения".socket_last_error());};
$datafromserv = socket_read($socket, 1024, PHP_NORMAL_READ);
socket_send($socket, $datatoserv, 10, MSG_EOF);
socket_close($socket);
if($datafromserv==""){die("Ничего не получено");};

if($datafromserv=="NULL\n"){$date="Ни одного сеанса не было";$files=NULL;}else{
  $date = substr($datafromserv,strpos($datafromserv,'date:\'')+6,19);
  $files = json_decode(str_replace(",}","}",str_replace("'","\"",substr($datafromserv, strpos($datafromserv, 'files:')+6,strrpos($datafromserv,'}')-(strpos($datafromserv, 'files:')+6)))),true);
}

?>                                                                                     

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>SmartServ</title>
    <link rel="stylesheet" href="mdl/material.min.css">
    <script src="mdl/material.min.js"></script>
  </head>
  <body>
      <center>
 <table style="border-spacing:200px 5px;margin-top:70px;margin-bottom:70px;"><tr align=center><td>Видео</td><td>Фото</td></tr><tr><td>

<?php

$flistvid="";
$flistimg="";
if($files!=NULL){
  foreach($files as $filename => $filedate)
  {
  	$filedateout = "";
  	if($filedate=='!recording'){
  		$filedateout = "<font color=red>Идёт запись</font>";
  	}elseif(strpos($filedate,'!')>0){
      if (file_exists($fdir."/".$filename)) {$fsize='('.round((filesize($fdir."/".$filename)/1024/1024),3).') MB ';}else{$fsize="";};
  		$filedateout = "RPi: ".str_replace('!',$fsize.'<font color=gray> Serv: ', $filedate)."</font>";
  	}elseif(strlen($filedate)==19){
  		$filedateout=$filedate." (".round((filesize($fdir."/".$filename)/1024/1024),3).") MB";
  	};
  	$delbutton=" <form style=\"display:inline;\"><button type=\"submit\" formaction=\"/\" formmethod=\"POST\" name=\"delfile\" value=\"$filename\" class=\"mdl-button mdl-js-button mdl-button--icon\"><img src=\"mdl/delete.png\"></button></form><br>";

  	if(strpos($filename,'.h264')>0){$flistvid .= "<a href=\"$fdir/$filename\" download>$filename</a>&emsp;".$filedateout.$delbutton;};
  	if(strpos($filename,'.jpg')>0){$flistimg .= "<a href=\"$fdir/$filename\" target=\"_blank\"><img src=\"$fdir/$filename\" width=\"80px\" height=\"60px\" style=\"vertical-align:middle;\"></a>&emsp;".$filedateout.$delbutton;};
  	
  }
  unset($filename,$filedate,$filedateout,$delbutton);
}

if($flistvid==""){$flistvid="No videos";};
if($flistimg==""){$flistimg="No photo";};

echo($flistvid."</td><td>".$flistimg."</td></tr></table><br>Последний сеанс: ".$date);
?>
<form action="/" method="POST" style="margin-top:70px;margin-bottom:70px;"><input type=hidden name=act value=<?php echo($_POST['act']); ?> ><button type=submit name=ref value=true class="mdl-button mdl-js-button mdl-button--icon"><img src="mdl/refresh.png"></button></form>

        <form action="/" method="POST">
          <button type=submit name=act value=rec class="mdl-button mdl-js-button"<?php btst("rec"); ?>>Запись</button>&emsp;&emsp;&emsp;&emsp;
          <button type=submit name=act value=photo class="mdl-button mdl-js-button"<?php btst("photo"); ?>>Фото</button>&emsp;&emsp;&emsp;&emsp;
          <button type=submit name=act value=stop class="mdl-button mdl-js-button"<?php btst("stop"); ?>>Стоп</button>&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;
          <button type=submit name=act value=off class="mdl-button mdl-js-button"<?php btst("off"); ?>>Off</button>
        </form>
        <?php echo($mess); ?>
      </center>
  </body>
</html>