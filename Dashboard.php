<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cyber Scan</title>
</head>
<body>

<form method="POST" action="">
    <input type="text" name="website_url" placeholder="Enter URL" required>
    <button type="submit" name="submit_scan">START SCAN</button>
</form>

<?php
$percentage=0;

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
        sleep(2);
        
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
?>
<script>
    const percentageValue = <?php echo json_encode($percentage); ?>;
    setProgress(percentageValue);
</script>
<!-- Use a single ID to pass the percentage -->
<div id="riskData" data-percent="<?php echo $percentage; ?>" style="display:none;"></div>



    <!-- thi is ovrall risk -->
     <div class="wrapper">
     <div class ="container">
        <div class="outer">
        <div class="inner">
            <div id="nuber">65%</div>
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
    <circle cx="100" cy="200" r="85" />
</svg>
</div>


 <!-- this is malware risk -->
<div class ="container">
        <div class="outer">
        <div class="inner">
            <div id="nuber1"> <?php echo round($percentage); ?>%</div>
            <div class ="box">
            <div class="iconmal"></div>
            <div class="high">high risk</div>
           </div>

</div>
<h5 class="risk">MALWARE SCANE<h5>0 Threats</h5></h5>
<h1 class="icon2"></h1>
</div>

<svg width="200px" height="400px">
    <defs>
        <linearGradient id="linearGradient">
            <stop offset="0%" stop-color="darkorange"/>
            <stop offset="100%" stop-color="yellow"/>
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
            <div id="nuber"> 65%</div>
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
        <linearGradient id="linearGradient">
            <stop offset="0%" stop-color="darkorange"/>
            <stop offset="100%" stop-color="yellow"/>
        </linearGradient>
    </defs>
    <circle cx="100" cy="200" r="85" />
</svg>
</div>




 <!-- this is web attacks-->
  
<div class ="container">
        <div class="outer">
        <div class="inner">
            <div id="nuber"> 65%</div>
             <div class="nebox">
                <div class="webicon"></div>
                <div class="optimal">optimal</div>
            </div>     
</div>
<h5 class="risk">WEB ATTACKS<h5>2 BLOCKED</h5></h5>
<h1 class="icon4"></h1>
</div>

<svg width="200px" height="400px">
    <defs>
        <linearGradient id="linearGradient">
            <stop offset="0%" stop-color="darkorange"/>
            <stop offset="100%" stop-color="yellow"/>
        </linearGradient>
    </defs>
    <circle cx="100" cy="200" r="85" />
</svg>
</div>

    
</body>
</html>