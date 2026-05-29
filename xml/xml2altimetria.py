# xml2altimetria.py
# -*- coding: utf-8 -*-

import xml.etree.ElementTree as ET

class Svg(object):
    """
    Genera archivos SVG con polilíneas, líneas y texto
    """
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

from math import radians, sin, cos, sqrt, atan2

def haversine(lon1, lat1, lon2, lat2):
    R = 6371000  # Radio de la Tierra en metros
    dLat = radians(lat2 - lat1)
    dLon = radians(lon2 - lon1)
    a = sin(dLat/2)**2 + cos(radians(lat1)) * cos(radians(lat2)) * sin(dLon/2)**2
    return R * 2 * atan2(sqrt(a), sqrt(1 - a))

def main():
    xml_file = "rutasEsquema.xml"
    NS = {'u': 'http://www.uniovi.es'}

    try:
        raiz = ET.parse(xml_file).getroot()
    except (IOError, ET.ParseError):
        print("Error procesando el archivo XML")
        return

    rutas = raiz.findall('u:ruta', NS)

    for ruta in rutas:
        distancias = []
        altitudes = []
        nombres_hitos = [] 

        # Punto de inicio
        inicio_alt = float(ruta.findtext('u:inicio/u:coordenadas/u:altitud', namespaces=NS))
        distancias.append(0.0)
        altitudes.append(inicio_alt)

        distancia_acumulada = 0.0
        hitos = ruta.find('u:hitos', NS)

        prev_lon = float(ruta.findtext('u:inicio/u:coordenadas/u:longitud', namespaces=NS))
        prev_lat = float(ruta.findtext('u:inicio/u:coordenadas/u:latitud', namespaces=NS))

        for nodo in hitos:
            tag = nodo.tag.replace('{http://www.uniovi.es}', '')

            alt_texto = nodo.findtext('u:coordenadas/u:altitud', namespaces=NS)
            lon_actual = float(nodo.findtext('u:coordenadas/u:longitud', namespaces=NS))
            lat_actual = float(nodo.findtext('u:coordenadas/u:latitud', namespaces=NS))

            if tag == 'hito':
                dist_el = nodo.find('u:distancia', NS)
                unidad = dist_el.get('unidad')
                dist_valor = float(dist_el.text)
                if unidad == 'km':
                    dist_valor = dist_valor * 1000
            else:
                # puntoRuta: calcular con Haversine
                dist_valor = haversine(prev_lon, prev_lat, lon_actual, lat_actual)

            distancia_acumulada += dist_valor
            distancias.append(distancia_acumulada)
            altitudes.append(float(alt_texto))

            #if tag == 'hito':
                #nombre_hito = nodo.findtext('u:nombre', namespaces=NS)
                #nombres_hitos.append((distancia_acumulada, nombre_hito))

            prev_lon = lon_actual
            prev_lat = lat_actual

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
        
        # Cerrar polilínea
        suelo = sy(min_y)
        points.append(f"{sx(max_x):.2f},{suelo:.2f}")
        points.append(f"{sx(min_x):.2f},{suelo:.2f}")

        points_str = " ".join(points)

        svg = Svg()
        svg.addPolyline(points_str, stroke="red", strokeWith=2, fill="#ffcccc")

        svg.addLine(sx(min_x), sy(min_y), sx(max_x), sy(min_y), "black", 2)

        svg.addLine(sx(min_x), sy(min_y), sx(min_x), sy(max_y), "black", 2)

        # Escala horizontal
        svg.addText(f"{int(min_x)} m", sx(min_x), sy(min_y) + 20, fontSize=11)
        svg.addText(f"{int(max_x)} m", sx(max_x) - 20, sy(min_y) + 20, fontSize=11)

        # Escala vertical
        svg.addText(f"{int(min_y)} m", sx(min_x) - 55, sy(min_y) + 5, fontSize=11)
        svg.addText(f"{int(max_y)} m", sx(min_x) - 55, sy(max_y) + 5, fontSize=11)
        
        svg.addText("Distancia (m)", sx((min_x + max_x)/2), sy(min_y)+40, fontSize=16, style="text-anchor: middle;")

        svg.addText("Altitud (m)", sx(min_x)-40, sy((min_y + max_y)/2), fontSize=16, style="writing-mode: tb; glyph-orientation-vertical: 0;")
        for dist_hito, nombre in nombres_hitos:
            x = sx(dist_hito)
            y = sy(min_y)
            svg.addLine(x, y, x, y + 5, "gray", 1)
            svg.addText(nombre, x, y + 10, fontSize=10, style="writing-mode: tb; text-anchor: start;")
                
        svg.raiz.attrib["width"] = str(width)
        svg.raiz.attrib["height"] = str(height)
        svg.raiz.attrib["viewBox"] = f"0 0 {width} {height}"

        nombre_archivo = ruta.findtext('u:altimetria', namespaces=NS)
        svg.escribir(nombre_archivo)
        print("Generado el archivo:",  nombre_archivo)


if __name__ == "__main__":
    main()
