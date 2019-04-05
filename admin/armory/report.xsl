<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html"/>
    <xsl:template match="/">
        <table id="report">
            <thead>
                <th title="Name">Name</th>
                <th title="Refill">Refill</th>
                <th title="Xanax">Xanax</th>
                <th title="Vicodin">Vicodin</th>
                <th title="Extasy">Extasy</th>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="5">Generated on: <xsl:value-of select="report/@created"/> for the last <xsl:value-of select="report/@days"/> days and <xsl:value-of select="report/@hours"/> hours</td>
                </tr>
            </tfoot>
            <tbody>
                <xsl:for-each select="report/user">
                     <xsl:sort select="refill[../@id != '0']" order="descending" data-type="number"/>
                        <tr>
                            <td><xsl:value-of select="name"/></td>
                            <td><xsl:value-of select="refill"/></td>
                            <td><xsl:value-of select="xanax"/></td>
                            <td><xsl:value-of select="vicodin"/></td>
                            <td><xsl:value-of select="extasy"/></td>
                        </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
</xsl:stylesheet>