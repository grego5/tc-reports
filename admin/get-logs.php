<?php
date_default_timezone_set('Europe/London');
ini_set('display_errors', 'on');
set_time_limit(0);
include 'simple_html_dom.php';
 
$start = strtotime('now');
$end = strtotime($_POST['end']);


$days = round((($start-$end) / 86400), 2);
$dd = (($start-$end) % 86400);
$hours = floor($dd / 3600);


$cfg = array (
   'tcuser'    => "nexustar",
    'tcpass'    => "WrEjuS32",
   // 'tcuser'    => "shiznit",
 //  'tcpass'    => "19gjmPTW89",
    'members' => array()
);

function str_match($pattern, $string, $x = null) {
    $matches = array();
    if (preg_match($pattern, $string, $matches)) {
        if (empty($x)) {
            return end($matches);
        } else return $matches[$x];
    }
    else return false;
}

function str_match_all($pattern, $string, $x = null, $y = null) {
    $matches = array();
    if (preg_match_all($pattern, $string, $matches)) {
        if (empty($x)) {
            if (empty($y)) return end($matches);
            else return $matches[count($matches)-1][$y];
        } else {
            if (empty($y)) return $matches[$x];
            else return $matches[$x][$y];
        }
    }
    else return false;
}


function make_ch() {
    global $cfg;
    $cfg['ch'] = curl_init();
    curl_setopt_array($cfg['ch'], array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_BINARYTRANSFER => true,
        CURLOPT_COOKIEFILE => 'cookie',
        CURLOPT_COOKIE => "mode=mobile; jsoff=on",
        CURLOPT_DNS_CACHE_TIMEOUT => 10,
        CURLOPT_ENCODING => "gzip",
    ));
}

function curl_login() {
    global $cfg;
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_BINARYTRANSFER  => true,
        CURLOPT_COOKIEJAR       => 'cookie',
        CURLOPT_COOKIE          => "mode=mobile; jsoff=on",
        CURLOPT_ENCODING        => "gzip",
        CURLOPT_URL             => "http://www.torn.com/authenticate.php",
        CURLOPT_POSTFIELDS      => "player=".$cfg['tcuser']."&password=".$cfg['tcpass']
    ));
            
    $result = curl_exec($ch); curl_close($ch);
    if(!$result) return false;
    if (preg_match('/Username or password incorrect/', $result)) {
        die ("Supplied TC username or password are incorrect");
    }
    return true;
}

function curl_get($path) {
    global $cfg;
    curl_setopt($cfg['ch'], CURLOPT_URL, $path);
    if(!($result = curl_exec($cfg['ch']))){
        echo curl_error($cfg['ch'])."\r\n";
        return false;
    }
    
    if (curl_getinfo($cfg['ch'], CURLINFO_SIZE_DOWNLOAD) < 2500) {
        curl_close($cfg['ch']);
        curl_login();
        make_ch();
        return curl_get($path);
    }
    return $result;
}

function get_members() {
    global $cfg;
    make_ch();
    while (!($result = curl_get("http://www.torn.com/factions.php?step=your&action=members"))) sleep(1);

    if (!($fac_id = str_match("/factionID=([0-9]+)/", $result))) {
        die("Torn city is not available");
    };

    $rows = str_match_all('#(<tr class="bgAlt1">|<tr class="bgAlt2">)(.+)</tr>#sU', $result);
    foreach($rows as $row) {
        array_push($cfg['members'], array(
           'id' =>  intval(str_match('#XID=([0-9]+)#', $row)),
           'name' => str_match('#XID.+b>(.+)</b.+</d#', $row),
           'days'   => (float) round(8640 / (time() - strtotime("today")) + (int)str_match_all('#>-*(\d+)<#', $row, null, 2), 2)
        ));
    }
}


function get_logs($a=0,$p=5){
    $results = array();
    $n = $p*25+$a;
    $mh = curl_multi_init();
    $still_running = 0;
    
    while (count($results) < $p)
    {
        for($i=$a; $i<$n; $i+=25)
        {
            $ch[$i] = curl_init();
            curl_setopt_array($ch[$i], array(
                CURLOPT_URL => "http://www.torn.com/factions.php?step=your&news=2&start=$i",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_BINARYTRANSFER => true,
                CURLOPT_COOKIEFILE => 'cookie',
                CURLOPT_COOKIE => "mode=mobile; jsoff=on",
                CURLOPT_DNS_CACHE_TIMEOUT => 10,
                CURLOPT_ENCODING => "gzip",
            ));
            curl_multi_add_handle($mh, $ch[$i]);
        };
        
        do {
            curl_multi_exec($mh, $still_running);
            usleep(200000);
        } while ($still_running > 0);
        for($i=$a; $i<$n; $i+=25)
        {
            $result = curl_multi_getcontent($ch[$i]);
            if (curl_getinfo($cfg['ch'], CURLINFO_SIZE_DOWNLOAD) < 2500)
            {
                curl_login();
                $a = $i;
                continue 2;
            }
            $results[$i] = $result;
        };
    };
    curl_multi_close($mh);
    $logs = array();
    $matches = array();
    foreach ($results as $result)
    {
        $rows = str_get_html($result)->find('table', -1)->find('tr[class=bgAlt1], tr[class=bgAlt2]');
        foreach ($rows as $i => $row) {
            if ($i === 0) continue;
            $date = preg_replace('/\s+|\r\n/', ' ', $row->find('td', 0)->plaintext); // remove spaces from the date
            $ex[0] = explode(" ",$date, 3); // break the date and time
            $ex[1] = explode("/", $ex[0][0]); // break the date
            $date = $ex[1][2]."-".$ex[1][1]."-".$ex[1][0]." ".$ex[0][1]." ".$ex[0][2]; // change date format
            $event = $row->find('td', 1)->plaintext; // strip the tags from the event
            $ex = explode(" ", $event, 4); // break the event into 4 parts     
            preg_match_all("#XID=[0-9]+|Someone#", $row->find(td, 1), $matches, 2); // get the event participants
            if (preg_match("/#[0-9]+/", $ex[1]) !== 0) { // check if it's chain bonus attack
                array_push($logs, array(
                    'date'      => $date,
                    'attacker'  => preg_replace("/XID=/", '', $matches[0][0]),
                    'result'    => 'bonus',
                    'bonus'     => ltrim($ex[1], "#")
                ));
                continue;
            };

            switch ($ex[1]) // if its not bonus, get the event action
            {
                case "mugged":
                case "hospitalized":
                case "attacked":
                $action = $ex[1];
            };

            if (isset($ex[3]))
            {
                switch($ex[3])
                {
                    case "and lost": $action = "lost"; break;
                    case "(Retaliation Bonus)": $action = "retaliation"; break;
                    case "and stalemated":  $action = "stalemated"; break;
                }
            }

            array_push($logs, array(
                'date'      => $date,
                'attacker'  => preg_replace("/XID=/", '', $matches[0][0], 1),
                'defender'  => preg_replace("/XID=/", '', $matches[1][0], 1),
                'result'    => $action,
            ));
        }
    };
    return $logs;
};

get_members();

$con=mysqli_connect('localhost', 'curl', 'H8cwZbNm46y7Ft3n', 'curl');
if (mysqli_connect_errno($con)) echo "Failed to connect to MySQL: " . mysqli_connect_error();

foreach ($cfg['members'] as $i => $member) {
	$update="INSERT INTO players (ID, name) VALUES ('$member[id]','$member[name]')
			  ON DUPLICATE KEY UPDATE name='$member[name]'";
	if (!mysqli_query($con,$update)) die('Error: ' . mysqli_error());
        
        $cfg['members'][$i]['notes'] = mysqli_query($con,"SELECT notes FROM players WHERE ID='$member[id]'")->fetch_object()->notes;
}
mysqli_close($con);

array_push($cfg['members'], array('name'=>'Summary','id'=>0));

$report = new SimpleXMLElement('<report/>');
$report->addAttribute('created', date('j/n/y g:i A', $start));
$report->addAttribute('days', floor($days));
$report->addAttribute('hours', $hours);
foreach ($cfg['members'] as $member)
{
        $node = $report->addChild('user');
            $node->addAttribute('id', $member['id']);
            $node->addChild('name', $member['name']);
            $node->addChild('total', 0);
            $node->addChild('average', 0);
            $node->addChild('hosps', 0);
            $node->addChild('attacks', 0);
            $node->addChild('mugs', 0);
            $node->addChild('lost', 0);
            $node->addChild('defends', 0);
            $node->addChild('retals', 0);
            $node->addChild('stalemates', 0);
            $node->addChild('notes', $member['notes']);
            $bonus = $node->addChild('bonus', 0);
                $bonus->addAttribute('num', 'total');
            $bonus = $node->addChild('bonus', 0);
                $bonus->addAttribute('num', '10');
            $bonus = $node->addChild('bonus', 0);
                $bonus->addAttribute('num', '25');
            $bonus = $node->addChild('bonus', 0);
                $bonus->addAttribute('num', '50');
            $bonus = $node->addChild('bonus', 0);
                $bonus->addAttribute('num', '75');
            $bonus = $node->addChild('bonus', 0);
                $bonus->addAttribute('num', '100');
};

function summerize($a = 0, $p=5)// scan the attack logs and fill report
{ 
    global $end, $report, $start;// ending point and xml skeleton
    static $prev = null;

    $logs = get_logs($a); // get an array version of the attack logs at specified page
    foreach ($logs as $log) 
    {
            $date = strtotime($log['date']); // get the date of the attack log
            if ($date <= $end) return; // exit if reached the end of specified rage
            if ($date > $start) {
                $prev = $log;
                continue;
            }
            if (!empty($prev) && 0 === count(array_diff($prev, $log))) {
                $prev = $log;
                continue; //skip on duplicate
            }
            $player = $report->xpath('//user[@id="'.$log["attacker"].'"]'); // gets the player of required id
            if (!isset($player[0]))  
            {
                if ($log['result'] === 'lost') {
                    $player = $report->xpath('//user[@id="'.$log["defender"].'"]');
                    $player[0]->defends += 1;
                    $player[0]->total += 1;
                }
                continue;
            }
            $player[0]->total += 1;
            switch ($log['result'])
            {
                case 'hospitalized': $player[0]->hosps += 1; break;
                case 'attacked': $player[0]->attacks += 1; break;
                case 'mugged': $player[0]->mugs += 1; break;
                case 'lost': $player[0]->lost += 1; break;
                case 'retaliation': 
                    $player[0]->retals += 1; 
                    $player[0]->hosps += 1;
                    break;
                case 'stalemated': $player[0]->stalemates += 1; break;
                case 'bonus':
                    $bonus = $player[0]->xpath('bonus[@num = "'.$log["bonus"].'"]');
                    $bonus[0][0] += 1;
                    $bonus = $player[0]->xpath('bonus[@num = "total"]');
                    $bonus[0][0] += 1;
                    $player[0]->total -= 1;
                    break; 
            }
            $prev = $log; //sets the last written log to be next starting point
    }
    $n = $p*25+$a;
    summerize($n); // increments the page number
}

summerize();

//avergage
foreach ($cfg['members'] as $member) {
    $player = $report->xpath('//user[@id ="'.$members[$i]['id'].'"]');
    $p = $members[$i]['days'] < $days ? $members[$i]['days'] : $days;
    $player[0]->average = round($player[0]->hosps / $p, 1);
}

//total
$players = $report->xpath('//user[@id != "0"]');
$total = $report->xpath('//user[@id = "0"]');
foreach ($players as $player)
{
    $total[0]->total += $player->total;
    $total[0]->hosps += $player->hosps;
    $total[0]->attacks += $player->attacks;
    $total[0]->mugs += $player->mugs;
    $total[0]->lost += $player->lost;
    $total[0]->defends += $player->defends;
    $total[0]->retals += $player->retals;
    $total[0]->stalemates += $player->stalemates;
    $total[0]->total += $player->total;
    $tbonus = $total[0]->xpath('bonus');
    $bonus = $player->xpath('bonus');
    for($i=0; $i<6; $i++) $tbonus[$i][0] += $bonus[$i][0];
}
$total[0]->average = round($total[0]->hosps / ($days < 1 ? 1 : $days), 1);



file_put_contents('../reports/latest.xml', $report->asXML());

$xml = new DOMDocument();
$xml->load('../reports/latest.xml');

$xsl = new DOMDocument;
$xsl->load('../report.xsl');

$proc = new XSLTProcessor();
$proc->importStyleSheet($xsl);

echo $proc->transformToXML($xml);
curl_close($cfg['curl']);