# xml2altimetria.py
# -*- coding: utf-8 -*-

import xml.etree.ElementTree as ET

class Svg(object):
    """Generar archivos SVG con polilíneas, líneas y texto"""
    def __init__(self):
        self.raiz = ET.Element('svg', xmlns="http://www.w3.org/2000/svg")

    def addLine(self, x1, y1, x2, y2, stroke, strokeWith):
        line = ET.SubElement(self.raiz, 'line',
                             x1=str(x1), y1=str(y1),
                             x2=str(x2), y2=str(y2),
                             stroke=stroke)
        line.set("stroke-width", str(strokeWith))

    def addPolyline(self, points, stroke, strokeWith, fill):
        polyline = ET.SubElement(self.raiz, 'polyline',
                                 points=points,
                                 stroke=stroke,
                                 fill=fill)
        polyline.set("stroke-width", str(strokeWith))

    def addText(self, text, x, y, fontSize=14, style=""):
        attrs = {"x": str(x), "y": str(y), "font-size": str(fontSize)}
        if style:
            attrs["style"] = style
        ET.SubElement(self.raiz,'text', attrs).text = text

    def escribir(self, nombreArchivoSVG):
        arbol = ET.ElementTree(self.raiz)
        ET.indent(arbol)
        arbol.write(nombreArchivoSVG, encoding='utf-8', xml_declaration=True)

    def ver(self):
        print("\nElemento raiz =", self.raiz.tag)
        for hijo in self.raiz.findall('.//'):
            print("Elemento =", hijo.tag, "Atributos =", hijo.attrib)

def main():
    xml_file = "rutasEsquema.xml"
    try:
        tree = ET.parse(xml_file)
        root = tree.getroot()
    except (IOError, ET.ParseError):
        print("Error al procesar el archivo XML")
        return
    
    ns = {'ns': 'http://www.uniovi.es'}

    rutas = root.findall('ns:ruta', ns)

    for ruta in rutas:
        distancias = []
        altitudes = []
        hitos_reales = [] 
        contador_hito = 1

        # Punto de inicio
        inicio_alt = float(ruta.findtext('ns:inicio/ns:coordenadas/ns:altitud', namespaces=ns))
        distancias.append(0.0)
        altitudes.append(inicio_alt)

        distancia_acumulada = 0.0
        hitos = ruta.find('ns:hitos', ns)

        for hito in hitos:
            altitud = float(hito.findtext('ns:coordenadas/ns:altitud', namespaces=ns))
            distancia = float(hito.findtext('ns:distancia', namespaces=ns))

            distancia_acumulada += distancia
            distancias.append(distancia_acumulada)
            altitudes.append(float(altitud))

            # Si no es punto anónimo -> tiene nombre
            nombre = hito.findtext('ns:nombre', namespaces=ns)
            if nombre:
                hitos_reales.append((f"H{contador_hito}", distancia_acumulada))
                contador_hito += 1

        margin = 80
        width = 1000
        height = 500

        min_x = min(distancias)
        max_x = max(distancias)
        min_y = min(altitudes)
        max_y = max(altitudes)

        def sx(x):
            return margin + (x - min_x) / (max_x - min_x) * (width - 2 * margin)

        def sy(y):
            return margin + (max_y - y) / (max_y - min_y) * (height - 2 * margin)

        points = [f"{sx(x):.2f},{sy(y):.2f}" for x, y in zip(distancias, altitudes)]
        
        #Cerrar polilínea
        suelo = sy(min_y)
        points.append(f"{sx(max_x):.2f},{suelo:.2f}")
        points.append(f"{sx(min_x):.2f},{suelo:.2f}")

        points_str = " ".join(points)

        svg = Svg()
        svg.addPolyline(points_str, stroke="red", strokeWith=2, fill="#ffcccc")

        #Línea eje x
        svg.addLine(sx(min_x), sy(min_y), sx(max_x), sy(min_y), "black", 2)
        #Línea eje y
        svg.addLine(sx(min_x), sy(min_y), sx(min_x), sy(max_y), "black", 2)

        #Escala horizontal
        svg.addText(f"{int(min_x)} m", sx(min_x), sy(min_y) + 40, fontSize=12)
        svg.addText(f"{int(max_x)} m", sx(max_x) - 20, sy(min_y) + 40, fontSize=12)

        #Escala vertical
        svg.addText(f"{int(min_y)} m", sx(min_x) - 55, sy(min_y) + 5, fontSize=12)
        svg.addText(f"{int(max_y)} m", sx(min_x) - 55, sy(max_y) + 5, fontSize=12)
        
        svg.addText("Distancia (m)", sx((min_x + max_x)/2), sy(min_y)+40, fontSize=16, style="text-anchor: middle;")
        svg.addText("Altitud (m)", sx(min_x)-40, sy((min_y + max_y)/2), fontSize=16, style="writing-mode: tb; glyph-orientation-vertical: 0;")
        
        #Señalamos los principales hitos de la ruta con las etiquetas 
        for etiqueta, dist_hito in hitos_reales:
            x = sx(dist_hito)
            y = sy(min_y)
            svg.addLine(x, y, x, y + 10, "gray", 2)
            svg.addText(etiqueta, x, y + 20, fontSize=12, style="text-anchor: middle;")
                
        svg.raiz.attrib["width"] = str(width)
        svg.raiz.attrib["height"] = str(height)
        svg.raiz.attrib["viewBox"] = f"0 0 {width} {height}"

        nombre_archivo = ruta.findtext('ns:altimetria', namespaces=ns)
        svg.escribir(nombre_archivo)
        print("Generado el archivo:",  nombre_archivo)


if __name__ == "__main__":
    main()
