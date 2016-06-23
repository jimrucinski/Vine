<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:template match="cheer">
    <HTML>
      <HEAD>
          <style>
             #master{display:block;margin-left:auto;margin-right:auto;padding:2em 2em 2em 2em;min-height:600px;width:800px;
              border:groove 10px #cccccc;background-image:url('images/cheer-4-peers.png');background-position:right bottom; background-repeat:no-repeat;}
              h1{font-family:Perpetua;font-size:2.75em;position:relative;left:-10%;top:.05em;letter-spacing:3px;white-space:nowrap;}
              h2{padding-top:0px;margin-top:0px;}
              #receive{border:ridge 3px #d1d19d;padding:.2em;margin-right:1em;background: linear-gradient(#ffffc9, #ffffff);}
              #cheer{margin-left:30%;}
              #claim{padding:.5em;line-height:1.2em;background:#ddffb3;background:rgba(223,255,181,0.3);border-radius: 15px;border:solid 1px #000000;}              
              ul{margin:0;padding:0px;}
              li{display:inline;font-size:large;}
              li label{font-weight:bolder;}
              p{font-size:large;}              
			 
			 }
		  </style>
		  <link rel="stylesheet" href="CertificatePrint.css" media="print"/>
        <TITLE></TITLE>
      </HEAD>
      <BODY>
          <div id="master">
            <div style="height:4em;background-color:#9de872;width:80%;float:right;">
                <h1>PMA Cheers for Peers Cheer Card</h1>
            </div>     
			<div style="position:relative;margin-top:15%;width:100%;">	
		
		<ul style="display:table;width:100%;">
		<li style="display:table-cell;width:20%;">
		<div id="receive">
                <ul>
                    <li>
                        <label>Date:</label>
                    </li>
                    <li>
                          <xsl:value-of select="dateSubmitted"/>
                    </li>
                    
                </ul>
                <br/><br/>
                <ul>
                    <li><label>Individual or Group Receiving Cheers:</label></li>
                </ul>                
          
                    <xsl:value-of select="cheerFor"/>
        
                <p style="margin-top:6em;">
                    <strong>Submitted By:</strong><br/><xsl:value-of select="submittedBy"/>
                </p>
            </div>       </li>
		<li style="display:table-cell;width:50%;">
		 <div id="cheers">
                <h2>Why are they being cheered on?</h2>
                <div id="claim">
                    <xsl:value-of select="cheerReason" disable-output-escaping="yes"/>     
              </div>
			  </div>
		</li>
		</ul>
            
            </div>               
          </div>        
      </BODY>
    </HTML>
  </xsl:template>

</xsl:stylesheet>