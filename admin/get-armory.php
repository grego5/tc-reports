<?php
date_default_timezone_set('Europe/London');
ini_set('display_errors', 'on');
set_time_limit(0);
include 'simple_html_dom.php';
$mh = curl_multi_init();

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

function str_match_all($pattern, $string, $x = null) {
    $matches = array();
    if (preg_match_all($pattern, $string, $matches)) {
        if (empty($x)) {
            return end($matches);
        } else return $matches[$x];
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
    
    if (curl_getinfo($cfg['ch'], CURLINFO_SIZE_DOWNLOAD) < 3000) {
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
           'name' => str_match('#XID.+b>(.+)</b.+</d#', $row)
        ));
    }
}

function get_logs($a=0,$p=5) {
    $results = array();
    $n = $p*25+$a;
    global $mh;
    $still_running = 0;
    static $history = array();

    while (count($results) < $p)
    {
        for($i=$a; $i<$n; $i+=25)
        {
            $ch[$i] = curl_init();
            curl_setopt_array($ch[$i], array(
                CURLOPT_URL => "http://www.torn.com/factions.php?step=your&news=4&start=$i",
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
            if (curl_getinfo($ch[$i], CURLINFO_SIZE_DOWNLOAD) < 3000)
            {
                curl_login();
                $a = $i;
                continue 2;
            }
            $results[$i] = $result;
        }
    }

    $logs = array();
    foreach ($results as $i => $result) {
        $rows = str_match_all('#(bgAlt1">|bgAlt2">)\n(.+)</tr>#sU', $result);
        foreach ($rows as $row) {
            $type = '';
            $event = str_match('#</a> (.+) *(<a|</t)#U', $row, 1);
            switch ($event) {
                case "used 25 of the faction's points to refill their energy.": $type = 1; break;
                case "used one of the faction's Xanax items.": $type = 2; break;
                case "used one of the faction's Vicodin items.": $type = 3; break;
                case "used one of the faction's Ecstasy items.": $type = 4; break;
            }
            
            if ($type !== 0) {
                $d = array();
                preg_match("#(\d\d)/(\d\d)/(\d\d)<br>(\d{1,2}):(\d\d):(\d\d) ([AP]M)#", $row, $d);
                array_push($logs, array(
                    'id' => intval(str_match("#ID=(\d+)#", $row)),
                    'type' => $type,
                    'date' => "$d[3]-$d[2]-$d[1] $d[4]:$d[5]:$d[6] $d[7]"
                ));
            }
        }
    }
    return $logs;
}

get_members();

$con=mysqli_connect('localhost', 'curl', 'H8cwZbNm46y7Ft3n', 'curl');
if (mysqli_connect_errno($con)) echo "Failed to connect to MySQL: " . mysqli_connect_error();

foreach ($cfg['members'] as $i => $member) {
	$update="INSERT INTO players (ID, name) VALUES ('$member[id]','$member[name]')
			  ON DUPLICATE KEY UPDATE name='$member[name]'";
	if (!mysqli_query($con,$update)) die('Error: ' . mysqli_error());
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
        $node->addChild('refill', 0);
        $node->addChild('xanax', 0);
        $node->addChild('vicodin', 0);
        $node->addChild('extasy', 0);
};

function summerize($a = 0, $p=5) {// scan the attack logs and fill report
    global $end, $report, $start;
    static $prev = null;
    
    $logs = get_logs($a, $p); // get an array version of the attack logs at specified page
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
        
        $player = $report->xpath("//user[@id=$log[id]]"); // gets the player of required id
        switch ($log['type']) {
            case 1: $player[0]->refill += 1; break;
            case 2: $player[0]->xanax += 1; break;
            case 3: $player[0]->vicodin += 1; break;
            case 4: $player[0]->extasy += 1; break;
        }
        $prev = $log; //sets the last written log to be next starting point
    }
    $n = $p*25+$a;
    summerize($n, $p); // increments the page number
}

summerize(0, 10);
curl_multi_close($mh);

//total
$players = $report->xpath('//user[@id != 0]');
$total = $report->xpath('//user[@id = 0]');
foreach ($players as $player) {
    $total[0]->refill += $player->refill;
    $total[0]->xanax += $player->xanax;
    $total[0]->vicodin += $player->vicodin;
    $total[0]->extasy += $player->extasy;
}

$path = 'armory/reports/latest.xml';
file_put_contents($path, $report->asXML());

$xml = new DOMDocument();
$xml->load($path);

$xsl = new DOMDocument;
$xsl->load('armory/report.xsl');

$proc = new XSLTProcessor();
$proc->importStyleSheet($xsl);

echo $proc->transformToXML($xml);