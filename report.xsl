<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:output method="html"/>
    <xsl:template match="/">
        <table id="report">
            <thead>
                <th title="Name">Name</th>
                <th title="Total">Total</th>
                <th title="Hospitalizations">Hospitalizations</th>
                <th title="Average">Average</th>
                <th title="Attacks">Attacks</th>
                <th title="Mugs">Mugs</th>
                <th title="Lost">Lost</th>
                <th title="Retaliations">Retaliations</th>
                <th title="Stalemates">Stalemates</th>
                <th title="Defends">Defends</th>
                <th title="Chain bonus">Chain bonus</th>
                <th title="Notes">Notes</th>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="12">Generated on: <xsl:value-of select="report/@created"/> for the last <xsl:value-of select="report/@days"/> days and <xsl:value-of select="report/@hours"/> hours</td>
                </tr>
            </tfoot>
            <tbody>
                <xsl:for-each select="report/user">
                     <xsl:sort select="hosps[../@id != 'summary']" order="descending" data-type="number"/>
                        <tr>
                            <td class="report-text"><a class="edit" href="#{@id}"><xsl:value-of select="name"/></a></td>
                            <td class="report-num total"><xsl:value-of select="total"/></td>
                            <xsl:element name="td">
                                <xsl:choose>
                                    <xsl:when test="@id != 'summary'">
                                        <xsl:attribute name="class">report-num score</xsl:attribute>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:attribute name="class">report-num</xsl:attribute>
                                    </xsl:otherwise>
                                </xsl:choose>
                                <xsl:value-of select="hosps"/>
                            </xsl:element>
                            <td class="report-num"><xsl:value-of select="average"/></td>
                            <td class="report-num"><xsl:value-of select="attacks"/></td>
                            <xsl:element name="td">
                                <xsl:choose>
                                    <xsl:when test="@id != 'summary'">
                                        <xsl:attribute name="class">report-num mugs</xsl:attribute>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:attribute name="class">report-num</xsl:attribute>
                                    </xsl:otherwise>
                                </xsl:choose>
                                <xsl:value-of select="mugs"/>
                            </xsl:element>
                            <td class="report-num"><xsl:value-of select="lost"/></td>
                            <td class="report-num"><xsl:value-of select="retals"/></td>
                            <td class="report-num"><xsl:value-of select="stalemates"/></td>
                            <td class="report-num"><xsl:value-of select="defends"/></td>
                            <xsl:element name="td">
                                <xsl:attribute name="class">report-num tooltip</xsl:attribute>
                                <xsl:attribute name="title">10: <xsl:value-of select="bonus[@num='10']"/>
25: <xsl:value-of select="bonus[@num='25']"/>
50: <xsl:value-of select="bonus[@num='50']"/>
75: <xsl:value-of select="bonus[@num='75']"/>
100: <xsl:value-of select="bonus[@num='100']"/>
                                </xsl:attribute>
                                <xsl:value-of select="bonus[@num='total']"/>
                            </xsl:element>
                            <td class="report-text" id="notes-{@id}"><xsl:value-of select="notes"/></td>
                        </tr>
                </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>
</xsl:stylesheet>