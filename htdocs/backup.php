<?php

print '<div class="blink_me" style="float:left;width:100%;height:30px;padding-top:5px;color:#FFFFFF;background-color:#f20d02;font-size:12px;text-align:center;padding-bottom:5px;margin-top:5px;"><p style="margin:0;"><strong>ATTENTION VERSION BACKUP</strong><br />Aucune donn&eacute;e ne sera enregistr&eacute;e</p></div>'."\n";
				print '
					<script type="text/javascript">
                    jQuery(document).ready(function () {
                    	function blinker() {
							$(".blink_me").fadeOut(500);
							$(".blink_me").fadeIn(500);
						}
						setInterval(blinker, 2000);
                   });
                	</script>';
?>
