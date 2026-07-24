<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cyber Scan</title>
</head>
<body>

<form method="POST" action="">
    <div class="searc">
    <input id ="inpu" type="text" name="website_url" placeholder="Enter URL" required >
    <i class="fa-solid fa-magnifying-glass"></i>
    <button id="buttons"type="submit" name="submit_scan">START SCAN</button>
    
</div>

</form>

<?php
$malwareRisk=0;
$networkRisk=0;
$percentage=0;
$percentageweb=0;
$overallRisk=0;




// this is malware logic

if (isset($_POST['submit_scan'])) {
    $urlToScan = $_POST['website_url'];
    $vtApiKey = '7ff99ddb2875f98a35007c268a448631235e5055cf64ce6d85589cefd3a9d446';

    $ch1 = curl_init();
    curl_setopt($ch1, CURLOPT_URL, 'https://www.virustotal.com/api/v3/urls');
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_POST, true);
    curl_setopt($ch1, CURLOPT_POSTFIELDS, http_build_query(['url' => $urlToScan]));
    curl_setopt($ch1, CURLOPT_HTTPHEADER, ['x-apikey: ' . $vtApiKey]);
    
    $response1 = curl_exec($ch1);
    $result1 = json_decode($response1, true);

    if (isset($result1['data']['id'])) {
        $analysisId = $result1['data']['id'];
        sleep(6);
    
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, 'https://www.virustotal.com/api/v3/analyses/' . $analysisId);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, ['x-apikey: ' . $vtApiKey]);
        
        $response2 = curl_exec($ch2);
        $result2 = json_decode($response2, true);
        


        if (isset($result2['data']['attributes']['stats'])) {
            $stats = $result2['data']['attributes']['stats'];
            $malicious = $stats['malicious'] ?? 0;
            $suspicious = $stats['suspicious'] ?? 0;
            
            $total = $malicious + $suspicious + 
                     ($stats['undetected'] ?? 0) +
                     ($stats['harmless'] ?? 0) +
                     ($stats['timeout'] ?? 0);
                     
                    

            if ($total > 0) {
                $percentage = (($malicious + $suspicious) / $total) * 100;
                echo "<h3>Malware Risk: " . round($percentage, 2) . "%</h3>";
            }
        }
        curl_close($ch2);
    } else {
        echo "<p>Problem: Unable to fetch analysis results. Please check your API key and URL.</p>";
    }
    curl_close($ch1);
}
//This is network logic
 
if (isset($_POST['website_url'])) {
    $urlToScan = $_POST['website_url'];
    $parsedUrl = parse_url($urlToScan, PHP_URL_HOST) ? parse_url($urlToScan, PHP_URL_HOST) : $urlToScan;
    $targetIp = gethostbyname($parsedUrl);
    $abuseApiKey = '81a982cafaa107d7c8a99034de62c68634eda8569711ef1b2b7e967f9ce42094f5272bdfc8997bb9';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.abuseipdb.com/api/v2/check?ipAddress={$targetIp}&maxAgeInDays=90");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Key: ' . $abuseApiKey,
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    

    curl_close($ch);

    $networkRisk = 0;
    if (isset($result['data']['abuseConfidenceScore'])) {
        $networkRisk = $result['data']['abuseConfidenceScore'];
        if ($networkRisk > 100) $networkRisk = 100;
        if ($networkRisk < 0) $networkRisk = 0;
    }

    echo "Abuse Confidence Score: " . $networkRisk . "%<br>";
}

//This is web Attacks
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_scan'])) {
    $urlToScan = $_POST['website_url'];
    $urlScanApiKey = '019f855b-8aa0-706e-9474-33b8f84de359'; 

    $ch1 = curl_init();
    curl_setopt($ch1, CURLOPT_URL, 'https://urlscan.io/api/v1/scan/');
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_POST, true);
    curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode(['url' => $urlToScan, 'visibility' => 'public']));
    curl_setopt($ch1, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'API-Key: ' . $urlScanApiKey
    ]);
    
    $response1 = curl_exec($ch1);
    $result1 = json_decode($response1, true);
    curl_close($ch1);

    if (isset($result1['uuid'])) {
        $uuid = $result1['uuid'];
        sleep(6); 
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, 'https://urlscan.io/api/v1/result/' . $uuid . '/');
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'API-Key: ' . $urlScanApiKey
        ]);

        $response2 = curl_exec($ch2);
        curl_close($ch2);

        $result2 = json_decode($response2, true);
        $response2 = curl_exec($ch2);

        $score = 10;
        if (isset($result2['verdicts']['overall']['score'])) {
            $score = $result2['verdicts']['overall']['score'];
        } elseif (isset($result2['data']['verdicts']['overall']['score'])) {
            $score = $result2['data']['verdicts']['overall']['score'];
        }

        $percentageweb = ($score >= 0 && $score <= 100) ? $score : 0;

        

        echo '<h3 id="riskData3" data-percent3="' . $percentageweb . '">Web Attack Risk: ' . round($percentageweb, 2) . '%</h3>';
        
    } else {
        echo "<p>Problem: Unable to fetch scan results. Please check your API key and URL.</p>";
    }
    
}

$overallRisk = ($percentage + $networkRisk + $percentageweb) / 3;

    echo '<h2 id="overallRiskDisplay" data-target="' .round ($overallRisk ). '"></h2>';


?>
<script>
    const percentageValue = <?php echo json_encode($percentage); ?>;
    setProgress(percentageValue);
    const networkRiskvalue = <?php echo json_encode($networkRisk); ?>;
    setProgress(networkRiskvalue);
    const percentagewebvalue = <?php echo json_encode($percentageweb); ?>;
    setProgress(percentagewebvalue);
    const overallRiskvalue= <?php echo json_encode($overallRisk); ?>;
    setProgress(overallRiskvalue);
</script>

<!-- Use a single ID to pass the percentage -->
<div id="riskData" data-percent="<?php echo $percentage; ?>" style="display:none;"></div>
<div id="riskData2" data-percent2="<?php echo $networkRisk; ?>" style="display:none;"></div>
<div id="riskData3" data-percent3="<?php echo $percentageweb; ?>" style="display:none;"></div>
<div id="riskData4" data-percent4="<?php echo $overallRisk; ?>" style="display:none;"></div>




    <!-- thi is ovrall risk -->
     <div class="wrapper">
     <div class ="container">
        <div class="outer">
        <div class="inner">
            <div id="nuber1"><?php echo round($overallRisk); ?>%</div>
           <div class ="box">
            <div class="boxicon"></div>
            <div class="high">high risk</div>
        </div>                 
</div>
<h5 class="risk">OVERALL RISK<h5>High (85%)</h5></h5>
<h1 class="icon"></h1>
</div>

<svg width="200px" height="400px">
    <defs>
        <linearGradient id="linearGradient">
            <stop offset="0%" stop-color="darkorange"/>
            <stop offset="100%" stop-color="yellow"/>
        </linearGradient>
    </defs>
    <circle id="overrisk" cx="100" cy="200" r="85" />
</svg>
</div>


 <!-- this is malware risk -->
<div class ="container">
        <div class="outer">
        <div class="inner">
            <div id="nuber1"> <?php echo round($percentage); ?>%</div>
            <div class ="box">
            <div class="iconmal"></div>
            <div class="high">Clean</div>
           </div>

</div>
<h5 class="risk">MALWARE SCANE<h5>0 Threats</h5></h5>
<h1 class="icon2"></h1>
</div>

<svg width="200px" height="400px">
    <defs>
        <linearGradient id="linearGradient2">
            <stop offset="0%" stop-color="lime"/>
            <stop offset="100%" stop-color="lime"/>
        </linearGradient>
    </defs>
    <circle id="lodermal" cx="100" cy="200" r="85" />
</svg>
</div>

<!-- thi is network risk-->
     <div class="wrapper">
     <div class ="container">
        <div class="outer">
        <div class="inner">
            <div id="nuber1"><?php echo round($networkRisk); ?>%</div>
            <div class="nebox">
                <div class="neticon"></div>
                <div class="optimal">optimal</div>
            </div>
</div>
<h5 class="risk">NETWORK SECURITY<h5>SECURE(85%)</h5></h5>
<h1 class="icon3"></h1>

</div>

<svg width="200px" height="400px">
    <defs>
        <linearGradient id="linearGradient3">
            <stop offset="0%" stop-color="lime"/>
            <stop offset="100%" stop-color="lime"/>
        </linearGradient>
    </defs>
    <circle  id="loderma2"cx="100" cy="200" r="85" />
</svg>
</div>




 <!-- this is web attacks-->
  
<div class ="container">
        <div class="outer">
        <div class="inner">
            <div id="nuber1"> <?php echo $percentageweb; ?>%</div>
             <div class="nebox">
                <div class="webicon"></div>
                <div id ="mont"class="MONITOR">Alert</div>
            </div>     
</div>
<h5 class="risk">WEB ATTACKS<h5>2 BLOCKED</h5></h5>
<h1 class="icon4"></h1>
</div>

<svg width="200px" height="400px">
    <defs>
        <linearGradient id="linearGradient4">
            <stop offset="0%" stop-color="violet"/>
            <stop offset="100%" stop-color="violet"/>
        </linearGradient>
    </defs>
    <circle id="loderma3"cx="100" cy="200" r="85" />
</svg>
</div>

    
</body>
</html>