<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
   
  <xsl:template match="/">
      <xsl:param name="orderby" select="'submittedBy'"/>
      <xsl:value-of select="$orderby"/>
    <HTML>
      <HEAD>
          <style>
              body{font-family:calibri;}
              table{border:solid 1px #666666;}
             
              td{border:solid 1px #cccccc;padding:.25em;}
              tr:nth-child(even) {background: #eee}
              tr:nth-child(odd) {background: #FFF}
          </style>
        <TITLE></TITLE>
      </HEAD>
      
      <BODY>
          <table cellpadding="0" cellspacing="0">
              <thead>
                  <tr style="background-color:#000000;color:#ffffff;text-transform:capitalize;">
                      <th>date submitted</th>
                      <th>submitted by</th>
                      <th>cheers for</th>
                      <th style="width:50%;">cheer</th>
                  </tr>
            </thead>
                   <xsl:for-each select="cheer">
                        <xsl:sort select="*[name()=$orderby]" order="ascending"/>
                            <tr>
                                <td>

                                    <xsl:value-of select="dateSubmitted"/>
                                </td>
                                <td>
                                    <xsl:element name="a">
                                        <xsl:attribute name="href">
                                            CheersForPeers.php?cheerid=<xsl:value-of select="@id"/>
                                        </xsl:attribute>
                                        <xsl:attribute name="target">
                                            new
                                        </xsl:attribute>
                                        <xsl:value-of select="submittedBy"/>
                                    </xsl:element>

                                </td>
                                <td>
                                    <xsl:value-of select="cheerFor"/>
                                </td>
                                <td>
                                    <xsl:value-of select="cheerReason"/>
                                </td>

                            </tr>
                    </xsl:for-each>
              
          
          </table>
          
         
      </BODY>
    </HTML>
  </xsl:template>
  

</xsl:stylesheet>