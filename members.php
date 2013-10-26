<?php

include_once("includes/pegDB.class.php");
$db = new pegDB("server", "localhost", "USERNAME", "PASSWORD", 3, 4, true);


if(isset($_GET['timestamp'])&&is_numeric($_GET['timestamp'])) {
  $results = $db->query("SELECT first_name,last_name,student_id,student_faculty,student_level,created FROM gaming_signups
                                                                WHERE created > '".mysql_real_escape_string($_GET['timestamp'])."'");
  $array = array();
  while ($row = mysql_fetch_array($results)) {
    $array[] = $row;
    end($array);
    $array[key($array)]['relativeTime'] = RelativeTime($row['created']);
  }
  
  die(json_encode($array));
} else {
  $results = $db->query("SELECT first_name,last_name,student_id,student_faculty,student_level,created FROM gaming_signups                                                                       
                                  ORDER BY created DESC");  
}

function RelativeTime($timestamp){
  $difference = time() - $timestamp;
  $periods = array("sec", "min", "hour", "day", "week",
  "month", "years", "decade", "century");
  $lengths = array("60","60","24","7","4.35","12","10","100");
  
  if ($difference > 0) { // this was in the past
    $ending = "ago";
  } else { // this was in the future
    $difference = -$difference;
    $ending = "to go";
  }
  if($difference < 15) {
    $text = "just moments ".$ending;
  } else {
    for($j = 0; $difference >= $lengths[$j]; $j++) 
      $difference /= $lengths[$j];
    $difference = round($difference);
    if($difference != 1) $periods[$j].= "s";
    $text = "$difference $periods[$j] $ending";
  }
  return $text;
}

?>
<html>
<head>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <title>Gaming Society Members - Live</title>
    <script type="text/javascript">
       $(function() {
          /* For zebra striping */
          $("table tr:nth-child(odd)").addClass("odd-row");
          /* For cell text alignment */
          $("table td:first-child, table th:first-child").addClass("first");
          /* For removing the last border */
          $("table td:last-child, table th:last-child").addClass("last");
      });   
      
      var lastUpdate = 0;
      var odd = false;
      var delay = 5000;
      
      $(document).ready(function() {
        function update(){
          $.getJSON("members.php?timestamp="+lastUpdate, function(data) {
            //console.log(data);
            $.each(data, function(key, val) {
              $('#members tbody').prepend("<tr class='"+(odd ? "odd-row" : "")+" hidden'><td class='first'>"+val.student_id+"</td><td>"+val.first_name+"</td><td>"+val.last_name+"</td><td>"+val.student_level+"</td><td>"+val.student_faculty+"</td><td>"+val.relativeTime+"</td></tr>\n");
              odd = !odd;
              lastUpdate = val.created;
              delay = 5000;
              $("#count").text(parseInt($("#count").text())+1);
              $(".hidden").fadeIn(1000).removeClass("hidden");
            });
            
                      
          });
        }
        setInterval(function() { update(); }, delay );
        update();
        setTimeout(function() { delay = Math.floor(delay * 1.5); if(delay>30*60*1000) delay = 30*60*1000; }, 180000);
      });  
    </script>
    <style>
    h1,h2,h3 {
        margin-bottom: 10px;
        text-align: center;
  color: #333;
  text-shadow: rgba(0,0,0,0.2) 2px 2px 2px;
    }
      table {
    overflow:hidden;
    border:1px solid #d3d3d3;
    background:#fefefe;
    width:70%;
    margin:0 auto 0;
    -moz-border-radius:5px; /* FF1+ */
    -webkit-border-radius:5px; /* Saf3-4 */
    border-radius:5px;
    -moz-box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
    -webkit-box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
}
 
th, td {padding:5px 5px 5px; text-align:center; }
th {padding-top:10px; text-shadow: 1px 1px 1px #fff; background:#e8eaeb;}
td {border-top:1px solid #e0e0e0; border-right:1px solid #e0e0e0;} 
tr.odd-row td {background:#f6f6f6;}
td.first, th.first {text-align:left}
td.last {border-right:none;}
 
/*
Background gradients are completely unnecessary but a neat effect.
*/

thead {
  background: #ededed;
}
 
td {
    background: #fefefe;
    background: -moz-linear-gradient(100% 25% 90deg, #fefefe, #f9f9f9);
    background: -webkit-gradient(linear, 0% 0%, 0% 25%, from(#f9f9f9), to(#fefefe));
}

tr:hover {
    background: #e8eaeb !important;
}
 
tr.odd-row td {
    background: #f6f6f6;
    background: -moz-linear-gradient(100% 25% 90deg, #f6f6f6, #f1f1f1);
    background: -webkit-gradient(linear, 0% 0%, 0% 25%, from(#f1f1f1), to(#f6f6f6));
}
 
th {
    background: -moz-linear-gradient(100% 20% 90deg, #e8eaeb, #ededed);
    background: -webkit-gradient(linear, 0% 0%, 0% 20%, from(#ededed), to(#e8eaeb));
}
 
table {
    border-radius: 5px;
}
.hidden {
  display: none;
}
    </style>
</head>
<body>
<h1>Gaming Society Members - Live</h1>
<h3>Member Count: <span id="count">0</span></h3>
<table cellpadding="0" cellspacing="0" id="members">
<thead>
  <tr class="first"><th>ID</th><th>First</th><th>Last</th><th>Level</th><th>Faculty</th><th>Added</th></th>
</thead>
<tbody>
<?php

/*while($row=mysql_fetch_array($results)) {
  echo "<tr><td>".$row['student_id']."</td><td>".$row['first_name']."</td><td>".$row['last_name']."</td>"
        ."<td>".$row['student_level']."</td><td>".($row['student_faculty'])."</td><td>".RelativeTime($row['created'])."</td></tr>\n";
}*/

?>
</tbody>
</table>
</body>
</html>