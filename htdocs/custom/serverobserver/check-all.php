<?php 

	require 'config.php';
	dol_include_once('/serverobserver/class/observer.class.php');

	llxHeader();
	dol_fiche_head();
	
	$TFkSoc = ServerObserver::getAllThirdparty();
	
	echo '<table id="table-check-all" class="border" width="100%">
			<tr class="liste_titre">
				<td>'.$langs->trans('Company').'</td>
				<td>'.$langs->trans('Server').'</td>
				<td>'.$langs->trans('Status').'</td>
				<td>'.$langs->trans('Version').'</td>
				<td>'.$langs->trans('DocumentSize').'</td>
				<td>'.$langs->trans('Users').'</td>
				<td>'.$langs->trans('Modules').'</td>
			</tr>';
	
	foreach($TFkSoc as $fk_soc) {
		
		$societe = new Societe($db);
		$societe->fetch($fk_soc);
		
		$data = parse_url($societe->array_options['options_serverobserverchecker']);
		
		if(strpos($data['host'],'atm-consulting')!==false && strpos($data['host'],'srv')!==false) {
			list($dummy, $srv) = explode('.', $data['host']) ;
		}
		else if(strpos($data['host'],'atm-consulting')!==false) {
			$srv='srv1?';
		}
		else {
			$srv = $data['host'];
		}

		if(!empty($data['port']) && $data['port']!=80) $srv.=':'.$data['port'];
//var_dump($srv, $data);exit;
		echo '<tr fk_soc="'.$societe->id.'">
				<td>'.$societe->getNomUrl(1).'</td>
				<td>'.$srv.'</td>
				<td rel="status">...</td>
				<td rel="version">...</td>
				<td rel="document">...</td>
				<td rel="user">...</td>
				<td rel="module">'.img_info().'</td>
			</tr>';
		
		
	}
	
	echo '</table>';
	
	?><script type="text/javascript">

	$(document).ready(function() {
		checkAll();
		
//		setInterval('checkAll()',600000);
		
	});

	function sleep(miliseconds) {
	   var currentTime = new Date().getTime();

		while (currentTime + miliseconds >= new Date().getTime()) {
	   		null;   
		}
	}

	function checkOne(fk_soc) {
console.log('checkOne',fk_soc);
			$.ajax({
				url:"<?php echo dol_buildpath('/serverobserver/script/interface.php',1)?>"
				,data:{
					fk_soc:fk_soc
					,get:"status"
				}
				,dataType:"json"
				,timeout: 10000
			})
			.fail(function() {
				window.setTimeout(checkOne(fk_soc), 1000);
			})
			.done(function(data) {

				$item = $('tr[fk_soc='+data.fk_soc+']');
				$item.attr("ok",data.ok);
				
				if(data.ok) {
		//			console.log(data,$item);
					$item.find('td[rel=status]').html('<?php echo img_picto('','on')?>');
					$item.find('td[rel=version]').html('<a href="'+data.dolibarr.path.http+'" target="_blank">'+data.dolibarr.version+'</a>');
					$item.find('td[rel=user]').html('<?php echo img_picto('','object_user')?> '
						+ data.user.active);

					if(data.user.date_last_login) {
						var dateLL = new Date(data.user.date_last_login);
//console.log(dateLL);
						$item.find('td[rel=user]>img').attr('title', dateLL.toLocaleString());
					}

					var datasize = data.dolibarr.data.size; var postsize = 'M';
					if(datasize>1024) { 
						datasize = Math.round(datasize/1024); postsize='G'; 
						if(datasize>2) postsize+= '<?php echo img_warning()?>';
					}

					$item.find('td[rel=document]').html(datasize + postsize);

					$item.find('td[rel=module]>img').attr('title', data.module.join(', '));

					$item.find('td[rel=module]>img, td[rel=user]>img').tipTip({maxWidth: "700px", edgeOffset: 10, delay: 50, fadeIn: 50, fadeOut: 50});
				}
				else {
					console.log($item);
					$item.find('td[rel=status]').html('<?php echo img_picto('','error')?>');
				}


				sortTable($("table#table-check-all"));
				
			});

	}

	function checkAll() {

		var timeDelai = 500;

		$('tr[fk_soc]').each(function(i,item) {
			
			$item = $(item);
			var fk_soc = $item.attr('fk_soc');

			timeDelai+=500;

			window.setTimeout(checkOne(fk_soc), timeDelai);
			
		});
	}
	
	function sortTable($table){
		var rows = $table.find('>tbody>tr[fk_soc]').get();

		rows.sort(function(a, b) {

			var A = parseInt($(a).attr('ok'));
			var B = parseInt($(b).attr('ok'));

			if(A == 0) {
				return -1;
			}
			else if(A == 1 && B==0) {
				return 1;
			}
			

			return 0;
		});

		$.each(rows, function(index, row) {
			$table.children('tbody').append(row);
		});
	}
	
	</script><?php
	
	dol_fiche_end();
	llxFooter();
