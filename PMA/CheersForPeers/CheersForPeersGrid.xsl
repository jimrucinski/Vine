<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html" indent="yes"/>
    <xsl:param name="sortby" select="'submittedBy'"/>
    <xsl:param name="year" />
    <xsl:param name="direction" select="'descending'"/>
    
  <xsl:template match="/">
      
    <xsl:variable name="optDirection">
      <xsl:choose>
          <xsl:when test="$direction='ascending'">descending</xsl:when>
          <xsl:otherwise>ascending</xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
      

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
          <table cellpadding="0" cellspacing="0" class='responsive-stacked-table' id="PmaWorkRequestsTable" >
              <thead>
                  <tr style="text-transform:capitalize;">
                      <th>
                          <xsl:element name="a">
                              <xsl:attribute name="href">
                                  <xsl:value-of select="concat('.?sortby=dateSubmitted&amp;year=',$year,'&amp;direction=', $optDirection)"/>
                              </xsl:attribute>
                              date submitted
                          </xsl:element>
                      </th>
                      <th>                      
                        <xsl:element name="a">
                            <xsl:attribute name="href">
                                <xsl:value-of select="concat('.?sortby=submittedBy&amp;year=',$year,'&amp;direction=', $optDirection)"/>
                            </xsl:attribute>
                            submitted by
                        </xsl:element>
                      </th>
                      <th>                  
                              
                        <xsl:element name="a">
                            <xsl:attribute name="href">
                                <xsl:value-of select="concat('.?sortby=cheerFor&amp;year=',$year,'&amp;direction=', $optDirection)"/>
                            </xsl:attribute>
                            cheers for
                        </xsl:element>
                      </th>
                      <th style="width:50%;">cheer</th>
                  </tr>
            </thead>
                   <xsl:for-each select="cheer">
                        <xsl:sort select="*[name()=$sortby]" order="{$direction}"/>
                            <tr>
                                <td>
                                    <xsl:value-of select="dateSubmitted"/>
                                </td>
                                <td>
                                    <xsl:element name="a">
                                        <xsl:attribute name="href">
                                            ../../../PMA/CheersForPeers/CheersForPeers.php?cheerid=<xsl:value-of select="@id"/>
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
                                    <xsl:value-of select="cheerReason" disable-output-escaping="yes" />
                                </td>

                            </tr>
                    </xsl:for-each>
              
          
          </table>
          
         
      </BODY>
    </HTML>
  </xsl:template>
  

</xsl:stylesheet>