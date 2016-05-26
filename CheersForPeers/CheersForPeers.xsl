<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:template match="cheer">
    <HTML>
      <HEAD>
          <style>
             #master{display:block;margin-left:auto;margin-right:auto;width:1024px;padding:2em 2em 2em 2em; 
              border:groove 10px #cccccc;background-image:url('images/cheer-4-peers.png');background-position:right bottom; background-repeat:no-repeat;}
              h1{font-family:Perpetua;font-size:3em;position:relative;left:-10%;top:.05em;letter-spacing:3px;white-space:nowrap;}
              h2{padding-top:0px;margin-top:0px;}
              #receiver{position:absolute;top:200px;display:block;border:ridge 3px #84a372; width:320px; padding:1em; }
              #cheer{position:relative;dispaly:block;min-height:700px;margin-left:400px;top:80px;width:600px;}
              #claim{border:none orange 3px;padding:.5em;font-size:1.25em;line-height:1.3em;padding-bottom:100px;}
              
              ul{margin:0;padding:0px;}
              li{display:inline;font-size:large;}
              li label{font-weight:bolder;}
              p{font-size:large;}
              
          </style>
          <style type="text/css" media="print">
              -ms-transform:rotate(-90deg);
        -o-transform:rotate(-90deg);
        transform:rotate(-90deg);
              -moz-transform: scale(58);
          </style>
        <TITLE></TITLE>
      </HEAD>
      <BODY>
          <div id="master" style="">
            <div style="height:4em;background-color:#9de872;width:80%;float:right;">
                <h1>PMA Cheers for Peers Cheer Card</h1>
            </div>      
            <div id="receiver">
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
                
                <p>
                    <xsl:value-of select="cheerFor"/>
                </p>
                <p>
                    <strong>Submitted By:</strong><span><xsl:value-of select="submittedBy"/></span>
                </p>
            </div>       
            <div id="cheer">
                <h2>Why are they being cheered on?</h2>
                <div id="claim">
                    <xsl:value-of select="cheerReason" disable-output-escaping="yes"/>     
              </div>    
            </div>               
          </div>        
      </BODY>
    </HTML>
  </xsl:template>

</xsl:stylesheet>