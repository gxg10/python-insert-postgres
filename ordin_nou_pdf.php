<?php
//config
	session_start();
             
ini_set("display_errors",0);

           if ($_SESSION['basic_is_logged_in'] != true)
			           {  
                header('Location: index.php');
                exit;
                } 
	require_once("../config.inc.php");
	require_once("../connect.inc.php");

	
	define ('FPDF_FONTPATH',"../fpdf/font/");
	require_once('../fpdf/fpdf.php');
	
	//------------------------------------------------
	class PDF extends FPDF
	{
		function Footer()
		{					
			$this->setY(-15);
			$this->SetFont('Arial','',8);
//			$this->Cell(0,5,'Interfinbrok Corporation S.A. - Data raport:  '.date("d/m/Y  H:i:s").'   #  '.$this->PageNo().'/{nb}',0,0,'R');
		}
		function write_two_rows($cw,$txt1,$txt2)
		{
			$txt=$txt1;
			$this->Text($this->GetX()-$cw+($cw-$this->GetStringWidth($txt))/2,$this->GetY()+3,$txt);
			$txt=$txt2;
			$this->Text($this->GetX()-$cw+($cw-$this->GetStringWidth($txt))/2,$this->GetY()+7,$txt);	
		}
	}

$sql_registru=mysql_query("SELECT * FROM arena.registru WHERE order_no LIKE '$_POST[tichet]' AND account LIKE '$_POST[cont_grup]'") or die(mysql_error());
$rez_sql_registru=mysql_fetch_array($sql_registru);
$convert_data=strtotime("$rez_sql_registru[data]");
$data=date("d/m/Y H:i:s",$convert_data);
$data_ordin=date("d/m/Y",$convert_data);
$simbol=$rez_sql_registru['simbol'];
$side=$rez_sql_registru['side'];
$tip_simbol=$rez_sql_registru['tip_simbol'];
$tip_pret=$rez_sql_registru['tip_pret'];
$tiparit=$rez_sql_registru['tiparit'];
$uk8=$rez_sql_registru['trader'];



if      ($tip_simbol=="SHARE")
        $tip_actiune="Actiune";
elseif  ($tip_simbol=="SHARE-ATS")
        $tip_actiune="Actiune";
elseif  ($tip_simbol=="RIGHT")
        $tip_actiune="Drept";
elseif  ($tip_simbol=="SHARE-INT")
        $tip_actiune="Actiune-INT";
elseif ($tip_simbol=="STR-PRD")
        $tip_actiune="Prod-Struct";
        
$piata=$rez_sql_registru['piata'];
$account=$rez_sql_registru['account'];
$cantitate=number_format($rez_sql_registru['cantitate'],0,',','.');
$pret=$rez_sql_registru['pret'];
$order_no=$rez_sql_registru['order_no'];
$nr_registru=$rez_sql_registru['id_ordin'];


$sql_client=mysql_query("SELECT b1.Nume as bNume, b1.Prenume as bPrenume, b1.AnDecizie as bAnDecizie, b1.NrDecizie as bNrDecize, 
								b2.Nume as agNume, b2.Prenume as agPrenume, b2.AnDecizie as agAnDecizie, b2.NrDecizie as agNrDecize,  cl. *
							FROM arena.clienti cl
							LEFT JOIN arena.brokers b1 ON b1.CodBroker = cl.cont_broker
							LEFT JOIN arena.brokers b2 ON b1.AgentID = b2.ID 
							WHERE cl.cont_arena LIKE '$account'") or die(mysql_error());
$rez_sql_client=mysql_fetch_array($sql_client);
$nume=$rez_sql_client['nume'];
$comision=number_format($rez_sql_client['comision'],2,',','.');
$cont_broker=$rez_sql_client['cont_broker'];
$tip_cont=$rez_sql_client['tip_cont'];
$cod_client=$rez_sql_client['cod_client'];

$indent=$rez_sql_client['indent'];
$data_ordin_det=date("Y-m-d",strtotime('-1 day',$convert_data));
$sql_detineri=mysql_query("SELECT total_act FROM arena.detineri WHERE data='$data_ordin_det' AND indent LIKE '$indent' AND simbol LIKE  '$simbol'"
                 ."  order by data DESC LIMIT 1") or die(mysql_error());
$rez_sql_det=mysql_fetch_array($sql_detineri);
$actiuni=$rez_sql_det['total_act'];

//noile reguli de aplicare

if($uk8=="UK8GW2")
  $prefixGW = "GW2 ";

$decizia=$rez_sql_client['bNrDecize'].'/'.$rez_sql_client['bAnDecizie'];
$broker=$prefixGW .' '.$rez_sql_client['bNume'].' '.$rez_sql_client['bPrenume'];

$decizie_trader=$rez_sql_client['agNrDecize'].'/'.$rez_sql_client['agAnDecizie'];
$trader=$prefixGW .' '.$rez_sql_client['agNume'].' '.$rez_sql_client['agPrenume'];

 
$sql_soc=mysql_query("SELECT * FROM arena.piete WHERE simbol LIKE '$simbol'") or die(mysql_error());
$rez_sql_soc=mysql_fetch_array($sql_soc);
$nume_soc=$rez_sql_soc['nume_soc'];

$sql_ordin=mysql_query("SELECT * FROM arena.orders WHERE order_no LIKE '$_POST[tichet]'") or die(mysql_error());
$rez_sql_ordin=mysql_fetch_array($sql_ordin);
$valabilitate=$rez_sql_ordin['order_term'];
$tip_update=$rez_sql_ordin['tip_update'];	
$timp_update=$rez_sql_ordin['timp_update'];	

$open=strtotime($rez_sql_ordin[open_date]);
$open_date=date("d/m/Y",$open);






if ($valabilitate=="Open" || $valabilitate=="FOK") $open_date='';


if ($side=="buy") {
		  $parte='CUMPARARE';
		  $op='OPC';
		  }
		  
elseif ($side=="sell"){
		  $parte='VANZARE';
		  $op='OPV';
		  }
	$textComision="Comisionul contine toate taxele inclusiv cota CNVM de 0,08 % din valoarea cumpararii";
	
	if($convert_data>=strtotime("2014-09-16"))	  
		$textComision="Comisionul contine toate taxele inclusiv cota ASF de 0,06 % din valoarea cumpararii";
	
	
	//Instanciation of inherited class
	$pdf=new PDF('P','mm','A4');
	$pdf->SetMargins(20,10,10);
	$pdf->SetDisplayMode('real','continuous');
	$pdf->SetTitle('Ordin de vanzare/cumparare');
	$pdf->SetAuthor('Interfinbrok');
	$pdf->SetCreator('IS Dynamic PDF Generator');
	$pdf->SetSubject('Ordin de vanzare/cumparare - printat pentru piata');	
	$pdf->AliasNbPages();	
	$pdf->AddPage();
	
	//Logo
    $pdf->Image('../img/interfinbrok.jpg',130,10,53);
    //Arial bold 15
    $pdf->SetFont('Arial','B',8);
    //Move to the right
    $pdf->Write('8','INTERFINBROK CORPORATION S.A');
	//Line break
	$pdf->Ln(4);
	$pdf->SetFont('Arial','',8);
	$pdf->Write('8','RO900590 Constanta, Str. Calarasi, nr. 1');
	$pdf->Ln(4);
	$pdf->Write('8','D 2000/2003  tel:0241-639 071 / fax:0241-547.829');
	$pdf->Ln(1);
	
	$pdf->SetFont('Arial','B',10);
	$string="ORDIN DE ".$parte;
	$pdf->Cell(175,30,$string,'',0,'C',$fill);	
	$pdf->SetFont('Arial','',8);
	$pdf->Ln(18);
	//de aici incep tabelele

	//tabel
    $pdf->SetFillColor(255,255,255);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetLineWidth(.1);
    
    	$fill=0;
	$cw=30;
	//---cap tabel

	$pdf->Cell($cw+10,6,'DATA SI ORA ORDIN','LT',0,'L',$fill);	
	$pdf->Cell($cw+35,6,'NUME CLIENT','LT',0,'L',$fill);	
	$pdf->Cell(15,6,'Simbol','LTR',0,'L',$fill);	
	$pdf->Cell(55,6,'Emitent','LTR',0,'L',$fill);	
	$pdf->Ln(5);
	$pdf->SetFont('Arial',B,10);
	$pdf->Cell($cw+10,8,$data,'LR',0,'L',$fill);	
	$pdf->Cell($cw+35,8,$nume,'',0,'L',$fill);	
	$pdf->Cell(15,8,$simbol,'LR',0,'L',$fill);	
	$pdf->SetFont('Arial','',6); 
		if ($pdf->GetStringWidth($nume_soc)>50)
		    {
    		    $soc_explode=explode(" ",$nume_soc);
		    $pdf->Cell(55,8,$soc_explode[0].' '.$soc_explode[1].' '.$soc_explode[2],'LR',0,'L',$fill);	
		    }
		else{	
		    $pdf->Cell(55,8,$nume_soc,'LR',0,'L',$fill);	
		    }
	$pdf->Ln(7);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell($cw+10,6,'Numar ordin : '.$nr_registru,'LBR',0,'L',$fill);
	$pdf->Cell($cw+35,6,'Cont Piata : '.$account,'B',0,'L',$fill);
	$pdf->Cell(15,6,$tip_actiune,'LBR',0,'L',$fill);
	$pdf->Cell(55,6,'Piata BVB    Categ. : '.$piata,'LBR',0,'L',$fill);
	$pdf->Ln(6);

		if     ($tip_pret=="limita"){	 
					    $tip_pret_limita='X';
					    $tip_pret_piata='';
					    $pretul=number_format($pret, 4, ',', '.');
					    }
		elseif ($tip_pret=="piata") {	 
					    $tip_pret_piata='X';
					    $tip_pret_limita='';
					    $pretul='';
					    }
	

	$pdf->Cell($cw-5,6,'CANTITATE','L',0,'L',$fill);
	$pdf->Cell($cw-5,6,'PRET','L',0,'L',$fill);
	$pdf->Cell(15,6,'','',0,'C',$fill);
	$pdf->Cell($cw-8,6,'COMISION','L',0,'L',$fill);
	$pdf->Cell($cw-2,6,'VALABILITATE','L',0,'L',$fill);
	$pdf->Cell($cw-8,6,'MONEDA','L',0,'L',$fill);
	$pdf->Cell($cw+8,6,'INSTRUCTIUNE ORDIN','RL',0,'L',$fill);
$pdf->Ln(6);
	$pdf->SetFont('Arial',B,10);
	$pdf->Cell($cw-5,6,$cantitate,'L',0,'C',$fill);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell($cw+10,6,'Limita','LR',0,'L',$fill);
	$pdf->SetXY(60,62); 
	$pdf->Cell(3,3,$tip_pret_limita,'TRLB',0,'C',$fill);
	$pdf->SetFont('Arial',B,10);
	$pdf->SetXY(63,61);
	$pdf->Cell($cw-8,6,$pretul,'',0,'R',$fill);
	$pdf->Cell($cw-8,6,$comision.'%','L',0,'C',$fill);
	$pdf->Cell($cw-2,6,$valabilitate,'L',0,'C',$fill);
	$pdf->Cell($cw-8,6,'RON','L',0,'C',$fill);
	$pdf->Cell($cw+8,6,'','LR',0,'C',$fill);
$pdf->Ln(6);
	$pdf->SetFont('Arial','',8);
	$pdf->Cell($cw-5,6,'','LB',0,'L',$fill);
	$pdf->Cell($cw+10,6,'La piata','LB',0,'L',$fill);
	$pdf->SetXY(60,68); 
	$pdf->Cell(3,3,$tip_pret_piata,'TRLB',0,'C',$fill);
	$pdf->SetXY(63,67);
	$pdf->Cell($cw-8,6,'','B',0,'L',$fill);
	$pdf->Cell($cw-8,6,'','LB',0,'L',$fill);
	$pdf->Cell($cw-2,6,$open_date,'LB',0,'C',$fill);
	$pdf->Cell($cw-8,6,'','LRB',0,'L',$fill);
	$pdf->Cell($cw+8,6,'','LRB',0,'L',$fill);
$pdf->Ln(6);
	$pdf->Cell($cw-8,6,'CALITATE SSIF','L',0,'L',$fill);
	$pdf->Cell(3,6,'','R',0,'L',$fill);
	$pdf->Cell($cw+3,6,'PLASARE ORDIN','L',0,'L',$fill);
	$pdf->Cell(3,6,'','R',0,'L',$fill);
	$pdf->Cell($cw+3,6,'TIP CONT','L',0,'L',$fill);
	$pdf->Cell(5,6,'','R',0,'L',$fill);
	$pdf->Cell($cw-6,6,'DISCRETIONAR','TRL',0,'L',$fill);
	$pdf->Cell($cw-16,6,'INSIDER','TRL',0,'L',$fill);
	$pdf->Cell($cw+8,6,'PERSOANA AUTORIZATA','RL',0,'L',$fill);	
	
	
	
    if ($tip_cont=="Institutie Financiara")
	$tip_cont_inst='X';
    elseif ($tip_cont=="House")
	$tip_cont_house='X';	
    elseif ($tip_cont=="Staff")
	$tip_cont_staff='X';		
    elseif ($tip_cont=="Client")
	$tip_cont_client='X';			
	
$pdf->Ln(6);
	$pdf->Cell($cw-8,6,'Broker','L',0,'L',$fill);
    $pdf->SetXY(39,81); 
	$pdf->Cell(3,3,'X','TRLB',0,'C',$fill);
    $pdf->SetXY(42,79); 
	$pdf->Cell(3,6,'','R',0,'C',$fill);
	$pdf->Cell($cw+5,6,'Recomandare ASIF','L',0,'L',$fill);
    $pdf->SetXY(75,81); 
	$pdf->Cell(3,3,'','TRLB',0,'C',$fill);
    $pdf->SetXY(78,79); 
	$pdf->Cell(3,6,'','R',0,'C',$fill);	
	$pdf->Cell($cw-10,6,'Client','L',0,'L',$fill);
    $pdf->SetXY(93,81); 
	$pdf->Cell(3,3,$tip_cont_client,'TRLB',0,'C',$fill);
    $pdf->SetXY(96,79); 
	$pdf->Cell(4,6,'','',0,'C',$fill);	
	$pdf->Cell($cw-11,6,'Inst. Fin.','R',0,'L',$fill);
    $pdf->SetXY(113,81); 
	$pdf->Cell(3,3,$tip_cont_inst,'TRLB',0,'C',$fill);
    $pdf->SetXY(116,79); 
	$pdf->Cell(3,6,'','',0,'C',$fill);	
	$pdf->Cell($cw-6,6,'Da','LR',0,'L',$fill);
    $pdf->SetXY(128,81); 
	$pdf->Cell(3,3,'','TRLB',0,'C',$fill);
    $pdf->SetXY(131,79); 
	$pdf->Cell(3,6,'','',0,'C',$fill);		
    $pdf->SetXY(143,79); 
	$pdf->Cell($cw-16,6,'Da','LR',0,'L',$fill);
    $pdf->SetXY(150,81); 
	$pdf->Cell(3,3,'','TRLB',0,'C',$fill);
    $pdf->SetXY(154,79); 
	$pdf->Cell(3,6,'','',0,'C',$fill);	
	$pdf->Cell($cw+8,6,'','R',0,'C',$fill);			
	
$pdf->Ln(6);
    $pdf->SetFont('Arial','',8);
	$pdf->Cell($cw-8,6,'Dealer','LB',0,'L',$fill);
    $pdf->SetXY(39,87); 
	$pdf->Cell(3,3,'','TRLB',0,'C',$fill);
    $pdf->SetXY(42,85); 	
	$pdf->Cell(3,6,'','RB',0,'C',$fill);
	$pdf->Cell($cw+5,6,'Initiativa client','LB',0,'L',$fill);
    $pdf->SetXY(75,87); 
	$pdf->Cell(3,3,'X','TRLB',0,'C',$fill);
    $pdf->SetXY(78,85); 	
	$pdf->Cell(3,6,'','RB',0,'C',$fill);	
	$pdf->Cell($cw-10,6,'House','LB',0,'L',$fill);
    $pdf->SetXY(93,87); 
	$pdf->Cell(3,3,$tip_cont_house,'TRLB',0,'C',$fill);
    $pdf->SetXY(96,85); 	
	$pdf->Cell(4,6,'','',0,'C',$fill);	
	$pdf->Cell($cw-11,6,'Staff','B',0,'L',$fill);
    $pdf->SetXY(113,87); 
	$pdf->Cell(3,3,$tip_cont_staff,'TRLB',0,'C',$fill);
    $pdf->SetXY(116,85); 	
	$pdf->Cell(3,6,'','',0,'C',$fill);		
	$pdf->Cell($cw-6,6,'Nu','LBR',0,'L',$fill);
    $pdf->SetXY(128,87); 
	$pdf->Cell(3,3,'X','TRLB',0,'C',$fill);
    $pdf->SetXY(131,85); 	
	$pdf->Cell(2,6,'','',0,'C',$fill);		

        //insider nu
    $pdf->SetXY(143,85); 	
	$pdf->Cell($cw-16,6,'','LBR',0,'L',$fill);
	//insider patrat
    $pdf->SetXY(150,87); 
	$pdf->Cell(3,3,'','',0,'C',$fill);
	
    $pdf->SetXY(154,85); 	
	$pdf->Cell(3,6,'','B',0,'C',$fill);	
	$pdf->Cell($cw+8,6,'','RB',0,'L',$fill);	
	
$pdf->Ln(6);	
$d=99;
$s=76;
$azi=date("d/m/Y");

    $pdf->SetFont('Arial','',8);
    $pdf->Cell($d,6,'DENUMIRE CUSTODE','TLR',0,'L',$fill);	
    $pdf->SetFont('Arial','B',8);
	$pdf->Cell($s/2,6,'A.S.I.F.','RL',0,'C',$fill);
	$pdf->Cell($s/2,6,'Agent Tranzactionare','R',1,'C',$fill);

    $pdf->SetFont('Arial','B',10);		
	$pdf->Cell($d,5,'Interfinbrok Corporation','LBR',0,'L',$fill);	
    $pdf->SetFont('Arial','B',8);
	$pdf->Cell($s/2,5,$broker,'LRB',0,'R',$fill);
	$pdf->Cell($s/2,5,$trader,'RB',1,'L',$fill);

    $pdf->SetFont('Arial','B',8);
	$pdf->Cell($d,5,'Alte Instructiuni / Observatii :','L',0,'L',$fill);
    $pdf->SetFont('Arial','',7);
	$pdf->Cell($s*3/8-5,5,'Decizia CNVM:','L',0,'L',$fill);
    $pdf->SetFont('Arial','B',8);
	$pdf->Cell($s/8+5,5,$decizia,'RBL',0,'R',$fill);
	$pdf->Cell($s/8+5,5,$decizie_trader,'BR',0,'R',$fill);
	$pdf->Cell($s*3/8-5,5,'','R',1,'R',$fill);
	

    $pdf->SetFont('Arial','',7);
	$pdf->Cell($d,5,$textComision,'L',0,'L',$fill);
        $pdf->SetFont('Arial','',7);
    	$pdf->Cell($s,5,'Nr. Registru CNVM:','LR',1,'L',$fill);


		
    $pdf->SetFont('Arial','',7);
	$pdf->Cell($d,5,'Clientul detine la data '.$data_ordin.' un numar de '.$actiuni.' actiuni','L',0,'L',$fill);
#    $pdf->SetFont('Arial','B',8);
	$pdf->SetFont('Arial','',7);
	$pdf->Cell($s,5,'Atestat Profesional:','RL',1,'L',$fill);



	$pdf->Cell($d,5,$op.' in desfasurare','LR',0,'L',$fill);
    $pdf->SetFont('Arial','B',7);
	$pdf->SetXY(65,117); 
	$pdf->Cell(3,5,'Da','',0,'C',$fill);	
	$pdf->SetXY(70,118);
	$pdf->Cell(3,3,'','TRLB',0,'C',$fill);
	$pdf->SetXY(85,117); 
	$pdf->Cell(3,5,'Nu','',0,'C',$fill);	
	$pdf->SetXY(90,118);
	$pdf->Cell(3,3,'X','TRLB',0,'C',$fill);
	$pdf->SetXY(119,117); 
    $pdf->SetFont('Arial','',7);	
    	$pdf->Cell($s,5,'','RL',1,'C',$fill);




	
    $pdf->SetFont('Arial','',7);
	$pdf->Cell($d,5,'SSIF direct implicat in '.$op,'L',0,'L',$fill);
    $pdf->SetFont('Arial','B',7);
	$pdf->SetXY(65,122); 
	$pdf->Cell(3,5,'Da','',0,'C',$fill);	
	$pdf->SetXY(70,123);
	$pdf->Cell(3,3,'','TRLB',0,'C',$fill);
	$pdf->SetXY(85,122); 
	$pdf->Cell(3,5,'Nu','',0,'C',$fill);	
	$pdf->SetXY(90,123);
	$pdf->Cell(3,3,'X','TRLB',0,'C',$fill);
	$pdf->SetXY(119,122);
    $pdf->SetFont('Arial','',7);
    	$pdf->Cell($s,5,'_ _ _ _ _ _ _ _ _ _ _Semnaturi_ _ _ _ _ _ _ _ _ _ _','RL',1,'C',$fill);	
	
    $pdf->SetFont('Arial','',7);
	$pdf->Cell($d,5,'Modificari ale capitalului social','RL',0,'L',$fill);
	$pdf->SetFont('Arial','B',7);
	$pdf->SetXY(62,127); 
	$pdf->Cell(3,5,'Fuziuni','',0,'C',$fill);	
	$pdf->SetXY(70,128);
	$pdf->Cell(3,3,'','TRLB',0,'C',$fill);
	$pdf->SetXY(82,127); 
	$pdf->Cell(3,5,'Divizari','',0,'C',$fill);	
	$pdf->SetXY(90,128);
	$pdf->Cell(3,3,'','TRLB',0,'C',$fill);
	$pdf->SetXY(103,127);
	$pdf->Cell(3,5,'Altele','',0,'C',$fill);	
	$pdf->SetXY(110,128);
	$pdf->Cell(3,3,'X','TRLB',0,'C',$fill);
$pdf->SetFont('Arial','',8);
	$pdf->SetXY(119,127);
	$pdf->Cell($s,5,'','RL',1,'L',$fill);

$pdf->SetFont('Arial','',7);
	$pdf->Cell($d,5,'Alte observatii (ordin TF, numar inregistrare TF etc.):_ _ _ _ _ _ _ _ _)','BLR',0,'L',$fill);
$pdf->SetFont('Arial','',8);	
	$pdf->Cell($s,5,'Client:_ _ _ _ _ _ _ _ _ _ _','RBL',1,'L',$fill);	 
	
$pdf->SetFont('Arial','B',8);
	$pdf->Cell(175,5,'Persoana relevanta:','TLBR',1,'L',$fill);	    
#$pdf->SetFont('Arial','B',10);
#	$pdf->Cell(175,5,'S.S.I.F. INTERFINBROK CORPORATION S.A.','TLBR',1,'C',$fill);	
#	$pdf->Line($pdf->GetX(),$pdf->GetY()+5,$pdf->GetX()+175,$pdf->GetY()+5);  			
$pdf->Ln(10);		

		
/*starile ordinului*/
    $pdf->SetFillColor(200,200,200);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetDrawColor(0,0,0);
    $pdf->SetLineWidth(.1);
	$cw=30;
	$pdf->SetFont('Arial','',9);	

	

$sql_stare_ordin=mysql_query("SELECT * FROM arena.orders WHERE order_no LIKE '$_POST[tichet]' AND update_type!='Filled' ORDER BY update_time ASC LIMIT 1,30") or die(mysql_error());

while($rez_sql_stare_ordin=mysql_fetch_array($sql_stare_ordin))
	{
	
	$cw=35;
	
	$tip_up=$rez_sql_stare_ordin['update_type'];

	if ($tip_up=="New")
	{
    	$tip_update='Nou';
	}
	elseif ($tip_up=="Changed")
	{
	$changed='Modificare';
	$tip_update='Modificat';
	}
	elseif ($tip_up=="Deleted")
	{
	$tip_update='Anulat';
	$changed='Anulare';
	}
	elseif ($tip_up=="Rejected")
	{
	$tip_update='Anulat';
	$changed='Anulare';
	}
	else
	{
	$tip_update='Anulat';
	$changed='Anulare';
	}
	

	$pdf->SetFont('Arial','',10);	
	$pdf->Cell($cw-15,10,'','',0,'C',0);
	$pdf->Cell(0,4,$changed.' ordin nr.  '.$nr_registru,'',1);
	$pdf->SetFont('Arial','',10);	
	
	$pdf->Cell($cw-15,4,'','',0,'C',0);
	$pdf->Cell($cw-15,4,'Cantitate','TLBR',0,'C',1);
	$pdf->Cell($cw-15,4,'Pret','TLBR',0,'C',1);
	$pdf->Cell($cw+10,4,'Data si ora','TLBR',0,'C',1);	
	$pdf->Cell($cw-10,4,'Stare','TLBR',0,'C',1);	
	$pdf->Cell($cw+10,4,'Semnatura','TLBR',1,'C',1);	
	
	
	$pdf->SetFont('Arial','',10);		
	$timp_update=strtotime($rez_sql_stare_ordin[update_time]);	
	$timp_ordin=date("d/m/Y H:i:s",$timp_update);
	$price=number_format($rez_sql_stare_ordin['price'],4,',','.');
	$size=number_format($rez_sql_stare_ordin['size'],0,',','.');	
	
		$pdf->Cell($cw-15,10,'','',0,'C',0);
		$pdf->Cell($cw-15,10,$size,'TLBR',0,'C',0);
		$pdf->Cell($cw-15,10,$price,'TLBR',0,'C',0);
		$pdf->Cell($cw+10,10,$timp_ordin,'TLBR',0,'C',0);
		$pdf->Cell($cw-10,10,$tip_update,'TLBR',0,'C',0);
		$pdf->Cell($cw+10,10,'','TLBR',1,'C',0);
	$pdf->Line($pdf->GetX(),$pdf->GetY()+5,$pdf->GetX()+175,$pdf->GetY()+5);  		
	$pdf->Ln(10);
	
	}	
	$pdf->Ln(5);
	$pdf->Cell(0,4,'Executiile ordinului:','',1);
	$pdf->SetFont('Arial','',8);	
#header tabel
	$pdf->Cell($cw-15,4,'Cantitate','TLBR',0,'C',1);
	$pdf->Cell($cw-15,4,'Pret','TLBR',0,'C',1);
	$pdf->Cell($cw-5,4,'Calitate SSIF','TLBR',0,'C',1);	
	$pdf->Cell($cw,4,'Numar Tranzactie','TLBR',0,'C',1);
	$pdf->Cell($cw,4,'Data si ora','TLBR',0,'C',1);
	$pdf->Cell($cw,4,'Stare','TLBR',1,'C',1);


#continut tabel executii
$executii=("SELECT * FROM arena.conturi WHERE cont_arena LIKE '$account' AND order_no LIKE '$_POST[tichet]' ORDER BY data ASC");
$sql_executii_ordin=mysql_query($executii) or die(mysql_error());
$nr_exec=mysql_num_rows($sql_executii_ordin);

if ($nr_exec>0)
{
while($rez_sql_executii_ordin=mysql_fetch_array($sql_executii_ordin))                                                                                       
{                                                                                                                                                           

                                                                                                                                                            
$pret_executie=number_format($rez_sql_executii_ordin['valoare']/$rez_sql_executii_ordin['cantitate'],4,',','.');                                            
$cant_executata=number_format($rez_sql_executii_ordin['cantitate'],0,',','.');                                                                              
$nr_tranzactie=$rez_sql_executii_ordin['ticket'];

$sql_timp=mysql_query("SELECT update_time FROM arena.trades WHERE ticket LIKE '$nr_tranzactie' ");
$rez_sql_timp=mysql_fetch_array($sql_timp);

$timp_update=$rez_sql_timp[0];
$timp_ordin=date("d/m/Y H:i:s",strtotime($timp_update));

		$pdf->Cell($cw-15,4,$cant_executata,'TLBR',0,'C',0);
		$pdf->Cell($cw-15,4,$pret_executie,'TLBR',0,'C',0);
		$pdf->Cell($cw-5,4,'AGENT','TLBR',0,'C',0);
		$pdf->Cell($cw,4,$nr_tranzactie,'TLBR',0,'C',0);
		$pdf->Cell($cw,4,$timp_ordin,'TLBR',0,'C',0);
		$pdf->Cell($cw,4,'Executat '.$status,'TLBR',1,'C',0);
}

}
else {
$sql_executii_ordin=mysql_query("SELECT * FROM arena.output WHERE order_no LIKE '$_POST[tichet]' AND account LIKE '$account' ORDER BY data ASC") or die(mysql_error());
	
while($rez_sql_executii_ordin=mysql_fetch_array($sql_executii_ordin))
        {
	$status=$rez_sql_executii_ordin['status']=="partial"?"partial":"complet";
	$timp_update=$rez_sql_executii_ordin['data'];
	
	$an=substr($timp_update,0,4);
	$luna=substr($timp_update,4,2);
	$zi=substr($timp_update,6,2);
	$ora=substr($timp_update,8,2);
	$minut=substr($timp_update,10,2);
	$secunda=substr($timp_update,12,2);
	
	$timp_ordin=("$zi/$luna/$an $ora:$minut:$secunda");
	
	$pret_executie=number_format($rez_sql_executii_ordin['pret'],4,',','.');
	$cant_executata=number_format($rez_sql_executii_ordin['cantitate'],0,',','.');	
	$nr_tranzactie=$rez_sql_executii_ordin['no'];

		$pdf->Cell($cw-15,4,$cant_executata,'TLBR',0,'C',0);
		$pdf->Cell($cw-15,4,$pret_executie,'TLBR',0,'C',0);
		$pdf->Cell($cw-5,4,'AGENT','TLBR',0,'C',0);
		$pdf->Cell($cw,4,$nr_tranzactie,'TLBR',0,'C',0);		
		$pdf->Cell($cw,4,$timp_ordin,'TLBR',0,'C',0);
		$pdf->Cell($cw,4,'Executat '.$status,'TLBR',1,'C',0);
	}	
	
	
}

$directory='pdf_constanta';
$count=0;

if ($handle = opendir($directory))
{

while (false !== ($file = readdir($handle)))
    {
    mkdir("pdf_constanta/$nume");
    $count=$count+1;
     }
closedir($handle);
}

if (isset($_POST['print']))
{
$print_data=date("Y-m-d h:i:s");
$update_tiparit=mysql_query("UPDATE arena.registru SET tiparit='Da', print_data='$print_data' WHERE id_ordin='$nr_registru' ") or die(mysql_error());
}
	//afisare PDF	
$pdf->SetFont('Times','',12);
$pdf->Close();
$pdf->Output('pdf_constanta/'.$nume.'/'.$_POST[tichet].'.pdf','F');
?>
<a href="<?='pdf_constanta/'.$nume.'/'.$_POST[tichet].'.pdf'?>" target="_self">open</a>