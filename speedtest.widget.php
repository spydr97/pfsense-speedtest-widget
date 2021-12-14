<?php

/*
 * speedtest.widget.php
 *
 * Copyright (c) 2020 Alon Noy
 *
 * Licensed under the GPL, Version 3.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once("guiconfig.inc");

if ($_REQUEST['ajax']) { 
    $results = shell_exec("speedtest --format=json");
    if(($results !== null) && (json_decode($results) !== null)) {
        $config['widgets']['speedtest_result'] = $results;
        write_config("Save speedtest results");
        echo $results;
    } else {
        echo json_encode(null);
    }
} else {
    $results = isset($config['widgets']['speedtest_result']) ? $config['widgets']['speedtest_result'] : null;
    if(($results !== null) && (!is_object(json_decode($results)))) {
        $results = null;
    }
?>
<table class="table table-striped">
	<tr>
		<td><h4>Ping</h4></td>
		<td><h4>Jitter</h4></td>
		<td><h4>Download</h4></td>
		<td><h4>Upload</h4></td>
	</tr>
	<tr>
		<td><h4 id="speedtest-ping-latency">N/A</h4></td>
		<td><h4 id="speedtest-ping-jitter">N/A</h4></td>
		<td><h4 id="speedtest-download">N/A</h4></td>
		<td><h4 id="speedtest-upload">N/A</h4></td>
	</tr>
		<td>Server</td>
		<td id="speedtest-server-name">N/A</td>
		<td id="speedtest-server-ip">N/A</td>
		<td id="speedtest-server-location">N/A</td>
	</tr>
		<td>ISP</td>
		<td id="speedtest-isp">N/A</td>
		<td id="speedtest-external-ip">N/A</td>
		<td>-</td>
	</tr>
	<tr>
        <td>Date</td>
		<td colspan="3" id="speedtest-timestamp">&nbsp;</td>
	</tr>
	<tr>
        <td>Result</td>
		<td colspan="3" id="speedtest-result-id">&nbsp;</td>
	</tr>
</table>
<a id="updspeed" href="#" class="fa fa-refresh" style="display: none;"></a>
<script type="text/javascript">
function updateResult(results) {
    if(results != null) {
    	$("#speedtest-timestamp").html(new Date(results.timestamp));
        $("#speedtest-ping-jitter").html(results.ping.jitter.toFixed(4) + "<small> ms</small>");
    	$("#speedtest-ping-latency").html(results.ping.latency.toFixed(4) + "<small> ms</small>");
    	$("#speedtest-download").html((results.download.bandwidth / 125000).toFixed(4) + "<small> Mbps</small>");
    	$("#speedtest-upload").html((results.upload.bandwidth / 125000).toFixed(4) + "<small> Mbps</small>");
    	$("#speedtest-isp").html(results.isp);
    	$("#speedtest-external-ip").html(results.interface.externalIp);
    	$("#speedtest-server-name").html(results.server.name);
    	$("#speedtest-server-location").html(results.server.location);
    	$("#speedtest-server-ip").html(results.server.ip);
    	$("#speedtest-result-id").html(results.result.id);
    } else {
    	$("#speedtest-ts").html("Speedtest failed");
        $("#speedtest-ping-jitter").html("N/A");
    	$("#speedtest-ping-latency").html("N/A");
    	$("#speedtest-download").html("N/A");
    	$("#speedtest-upload").html("N/A");
    	$("#speedtest-isp").html("N/A");
    	$("#speedtest-external-ip").html("N/A");
    	$("#speedtest-server-name").html("N/A");
    	$("#speedtest-server-location").html("N/A");
    	$("#speedtest-server-ip").html("N/A");
    	$("#speedtest-result-id").html("N/A");
    }
}

function updateSpeedtest() {
    $('#updspeed').off("click").blur().addClass("fa-spin").click(function() {
        $('#updspeed').blur();
        return false;
    });
    $.ajax({
        type: 'POST',
        url: "/widgets/widgets/speedtest.widget.php",
        dataType: 'json',
        data: {
            ajax: "ajax"
        },
        success: function(data) {
            updateResult(data);
        },
        error: function() {
            updateResult(null);
        },
        complete: function() {
            $('#updspeed').off("click").removeClass("fa-spin").click(function() {
                updateSpeedtest();
                return false;
            });
        }
    });
}
events.push(function() {
	var target = $("#updspeed").closest(".panel").find(".widget-heading-icon");
	$("#updspeed").prependTo(target).show();
    $('#updspeed').click(function() {
        updateSpeedtest();
        return false;
    });
    updateResult(<?php echo ($results === null ? "null" : $results); ?>);
});
</script>
<?php } ?>
