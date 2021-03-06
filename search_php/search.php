<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  include './inc_php/dbcred.php';
  $searchParams = $_GET["search"];
  $cleanSearchParams = strtolower(preg_replace('/\s+/', '', strip_tags($searchParams)));
  $cleanSearchParams = preg_replace('/[^A-fa-f0-9\-]/', '', $cleanSearchParams);
  $cleanSearchParams = substr($cleanSearchParams,0,40);
  $response = array();
  $returnMsg = '';
  $errorFlag = false;
  $errorMsg = '';

  if(empty($cleanSearchParams)){
    $errorFlag = true;
    $errorMsg = 'Search box is empty.';
  } elseif (!preg_match('/^[0-9A-Fa-f]{40}.*/', $cleanSearchParams)){
    $errorFlag = true;
    $errorMsg = 'Not a fingerprint or message ID.';
  } else {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
      $errorFlag = true;
      $errorMsg = 'Server unavailable. Please try again.';
    }
    $sql = "SELECT * FROM magicpost_main WHERE filedir LIKE '$cleanSearchParams' OR fromkey LIKE '$cleanSearchParams' OR tokey LIKE '$cleanSearchParams'";
    $result = $conn->query($sql);
    $rows = array();
    $rowsLen = mysqli_num_rows($result);
    if ($rowsLen > 0) {
      $resultStr = ' result';
      if($rowsLen > 1){
        $resultStr = ' results';
      }
      $returnMsg = 'Found '.$rowsLen.$resultStr;
      echo '<div class="search-results-box"><div class="search-results-summary">'.$returnMsg.'</div>';
      echo '<table class="search-results">';
      echo '<thead id="result-thead"><tr><th></th><th>Message ID</th><th data-sort-default>Time</th><th>Sender fingerprint</th><th>Recipient fingerprint</th></tr></thead><tbody>';
      while($row = mysqli_fetch_assoc($result)) {
        //$rows[] = $row;
        $fromKeyLink = "https://keys.magicpad.io/pks/lookup?op=get&options=mr&search=0x".$row['fromkey'];
        $toKeyLink = "https://keys.magicpad.io/pks/lookup?op=get&options=mr&search=0x".$row['tokey'];
        echo '<tr>';
        echo '<td><a href="./view.php?post='.$row['filedir'].'" target="_blank" rel="noopener noreferrer nofollow">Raw</a><br><a href="./magicpad.php?post='.$row['filedir'].'" target="_blank" rel="noopener noreferrer nofollow">MagicPad</a></td>';
        echo '<td>'.chunk_split(chunk_split($row['filedir'], 4, ' '), 25, '<br>').'</td>';
        echo '<td>'.date("Y-m-d<\b\\r>H:i:s", intval($row['time'])).'</td>';
        echo '<td><a href="'.$fromKeyLink.'" target="_blank" rel="noopener noreferrer nofollow">'.chunk_split(chunk_split($row['fromkey'], 4, ' '), 25, '<br>').'</a></td>';
        echo '<td><a href="'.$toKeyLink.'" target="_blank" rel="noopener noreferrer nofollow">'.chunk_split(chunk_split($row['tokey'], 4, ' '), 25, '<br>').'</a></td>';
        echo '</tr>';
      }
      echo '</tbody></table></div>';
    } else {
      $returnMsg = 'No results found';
      echo '<div class="search-results-box"><div class="search-results-summary">'.$returnMsg.'</div></div>';
    }
    $conn->close();
  }

  if($errorFlag){
    echo '<div class="lip error active"><span>'.$errorMsg.'</span><button class="lip-exit"><img src="./assets/img/exit.svg"></button></div>';
    //$response['errors']  = $errorMsg;
  } else {
    //$response['success'] = $returnMsg;
    //$response['results'] = $rows;
  }
}


?>
