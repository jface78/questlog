	<style type="text/css" media="screen">
	<!--
	
	body {
	scrollbar-face-color: #333333;
	scrollbar-arrow-color: #6A6363; 
	scrollbar-3dlight-color: #6A6363; 
	scrollbar-highlight-color: #333333;
	scrollbar-shadow-color: #6A6363; 
	scrollbar-darkshadow-color: #333333;
	scrollbar-track-color: #333333;
	font-family: <? echo $FONTS; ?>; 
	font-weight: none;
	font-size: 10px;
	color: #333333;
	}
	
	a:link, a:active, a:visited {
	color: #cccccc;
	font-weight: bold;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	} a:hover { color: #ffffff; }
	
	a.contact:link, a.contact:active, a.contact:visited {
	color: #FFD75D;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	} a.contact:hover { color: #ffffff; }
	
	a.postlink:link, a.postlink:active, a.postlink:visited {
	color: #aaaaaa;
	font-weight: normal;
	font-size: <? echo $SMALL_FONT_SIZE; ?>;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.5mm;
	} a.postlink:hover { color: #FFD75D; }
	
	a.smlink:link, a.smlink:active, a.smlink:visited {
	color: #aaaaaa;
	font-weight: normal;
	font-size: <? echo $SMALL_FONT_SIZE; ?>;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.5mm;
	} a.smlink:hover { color: #FFD75D; }
	
	a.menulink:link, a.menulink:active, a.menulink:visited {
	color: #aaaaaa;
	font-weight: normal;
	font-size: <? echo $SMALL_FONT_SIZE; ?>;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.3mm;
	} a.menulink:hover { color: #ffffff; }
	
	a.loglink:link, a.loglink:active, a.loglink:visited {
	color: #000000;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	} a.loglink:hover { color: #eeeeee; }
	
	a.logtitle:link, a.logtitle:active, a.logtitle:visited {
	color: #222222;
	font-weight: bold;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	} a.logtitle:hover { color: #FFD75D; }
	
	a.postlink2:link, a.postlink2:active, a.postlink2:visited {
	color: #1F1F1F;
	font-weight: bold;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	} a.postlink:hover { color: #000000; }
	
	pre {
	line-height: 8px;
	}
	
	.main {
	color: #999999;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	}
	
	.list {
	line-height: 12px;
	}
	
	.posthead {
	color: #C4C4C4;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	}
	
	.txtposthd {
	color: #1F1F1F;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	}
	
	.imgposthd {
	line-height: 0px;
	margin: 0px;
	margin-top: 5px;
	}
	
	.text {
	color: #FFFFFF;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	}
	
	.quotation {
	color: #FFFFFF;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 12px;
	margin-left: 15px;
	margin-right: 80px;
	}
	
	.smtext {
	color: #777777;
	font-weight: normal;
	font-size: <? echo $SMALL_FONT_SIZE; ?>;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.5mm;
	line-height: 12px;
	}
	
	.newsframe {
	color: #ffffff;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 12px;
	}
	
	.formarea {
	color: #ffffff;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	}
	
	.form {
	color: #ffffff;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	}
	
	.log-bar {
	
	}
	
	.log-table { 
	color:	 #000000;
    font-weight:	 normal;
    font-size:	 10px;
	font-family:	 <? echo $FONTS; ?>;
	text-decoration:	 none;
	letter-spacing:	 0.1mm;
	}
	
	.log-head { border-bottom: <? echo $LOG_BORDER_COLOR; ?> 1px solid; }
	
	.log-left {
	border-bottom:	<? echo $LOG_BORDER_COLOR; ?> 1px solid;
	border-right:	<? echo $LOG_BORDER_COLOR; ?> 1px solid;
	border-left:	<? echo $LOG_BORDER_COLOR; ?> 1px solid;
	}
	
	.log-cell {
	border-bottom:	<? echo $LOG_BORDER_COLOR; ?> 1px solid;
	border-right:	<? echo $LOG_BORDER_COLOR; ?> 1px solid;
	}	
       
    .post-table {
	border-top:     <? echo $LOG_BORDER_COLOR; ?> 1px solid;
	border-left:  	<? echo $LOG_BORDER_COLOR; ?> 1px solid;
	border-right:  	<? echo $LOG_BORDER_COLOR; ?> 1px solid;
	}	
	
	.post-cell {
	border-top:     <? echo $LOG_BORDER_COLOR; ?> 1px solid;
	border-bottom:  <? echo $LOG_BORDER_COLOR; ?> 1px solid;
	}
	
	.post-text {
	color: #000000;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	margin-right: 20px;
	margin-left: 20px;
	margin-top: 20px;
	margin-bottom: 0px;
	}
	
	TABLE.table {
	color: #ffffff;
	font-weight: normal;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	line-height: 20px;
	}
	
	TABLE.borders {
	border-style:	solid;
	border-width:	1px;
	border-color: 	#000000;
	
	
	<?/*BORDER-TOP:    #9F9D8E 1px outset; 
	BORDER-LEFT:   #9F9D8E 1px outset; 
	BORDER-BOTTOM: #9F9D8E 1px inset; 
	BORDER-RIGHT:  #9F9D8E 1px inset;*/?>
	}
	
	INPUT.button {
	BORDER-TOP:    #848383 1px outset; 
	BORDER-LEFT:   #848383 1px outset; 
	BORDER-BOTTOM: #848383 1px inset; 
	BORDER-RIGHT:  #848383 1px inset;
	BACKGROUND-COLOR: #3B3B3B;
	font-size: 10px;
	font-weight: bold;
	color: #ffffff;
	font-family: <? echo $FONTS; ?>;
	}
	
	hr {
	border: 0;
	border-top: #222222 1px dashed;
	height: 1px;
	width: 100%;
	text-align: left;
	}
	
	.field {
	BORDER-TOP:    #848383 1px outset; 
	BORDER-LEFT:   #848383 1px outset; 
	BORDER-BOTTOM: #848383 1px inset; 
	BORDER-RIGHT:  #848383 1px inset; 
	BACKGROUND-COLOR: #000000;
	color: #ffffff;
	font-weight: bold;
	font-size: 10px;
	font-family: <? echo $FONTS; ?>;
	text-decoration: none;
	letter-spacing: 0.1mm;
	}
	
	//-->
	</style>