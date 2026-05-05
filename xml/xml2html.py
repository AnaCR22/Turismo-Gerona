# xml2html_xpath.py
# -*- coding: utf-8 -*-
"""
Genera InfoCircuito.html a partir de circuitoEsquema.xml
Solo guarda la información en <p> para procesar con JS posteriormente.
No incluye puntoOrigen ni tramos.
"""

import xml.etree.ElementTree as ET

def main():
    xml_path = "circuitoEsquema.xml"
    ns = {'u': 'http://www.uniovi.es'}

    try:
        raiz = ET.parse(xml_path).getroot()
    except (IOError, ET.ParseError) as e:
        print("Error al leer el XML:", e)
        return

    # Crear documento HTML básico
    html = ET.Element("html", lang="es")
    head = ET.SubElement(html, "head")
    ET.SubElement(head, "meta", charset="UTF-8")
    ET.SubElement(head, "title").text = "InfoCircuito"
    ET.SubElement(head, "link", rel="stylesheet", href="../estilo/estilo.css")
    body = ET.SubElement(html, "body")

    # --- Información general del circuito usando XPath ---
    campos = ["nombre", "longitud", "anchuraMedia", "fecha", "horaInicio",
              "numeroVueltas", "localidadProxima", "pais", "patrocinador",
              "vencedor", "tiempoVencedor"]
    
    nombreCampo = ["Nombre del circuito", "Longitud del circuito", "Anchura media del circuito", "Fecha de la carrera",
                   "Hora de incio de la carrera", "Número de vueltas", "Localidad más próxima", "País", "Patrocinador", "Vencedor", "Tiempo del vencedor"]

    counter = 0
    for campo in campos:
        elem = raiz.find(f".//u:{campo}", ns)  # XPath expression
        if elem is not None:
            if "cantidad" in elem.attrib:
                texto = f"{nombreCampo[counter]}: {elem.attrib.get('cantidad','')}"
                if "unidad" in elem.attrib:
                    texto += f" {elem.attrib.get('unidad')}"
            else:
                valor = elem.text.strip() if elem.text else ""
                texto = f"{nombreCampo[counter]}: {valor}"
            ET.SubElement(body, "p").text = texto
            counter = counter + 1

    # --- Referencias ---
    for ref in raiz.findall(".//u:referencias/u:referencia", ns):
        fuente = ref.attrib.get("fuente", "Desconocida")
        enlace = ref.attrib.get("enlace")
        descripcion = ref.text.strip() if ref.text else ""
        ET.SubElement(body, "p").text = f"{fuente}: {descripcion}"
        ET.SubElement(body, "p").text = f"{enlace}"

    # --- Fotografías ---
    for foto in raiz.findall(".//u:fotografias/u:fotografia", ns):
        descripcion = foto.attrib.get("descripcion", "").strip()
        url = foto.attrib.get("url", "").strip()
        ET.SubElement(body, "p").text = f"{descripcion}"
        ET.SubElement(body, "p").text = f"{url}"


    # --- Videos ---
    for video in raiz.findall(".//u:videos/u:video", ns):
        descripcion = video.attrib.get("descripcion", "").strip()
        url = video.attrib.get("url", "").strip()
        ET.SubElement(body, "p").text = f"{descripcion}"
        ET.SubElement(body, "p").text = f"{url}"

    # --- Clasificados ---
    for clasificado in raiz.findall(".//u:clasificados/u:clasificado", ns):
        if clasificado.text:
            ET.SubElement(body, "p").text = f"{clasificado.text.strip()}"

    # --- Escribir HTML ---
    arbol = ET.ElementTree(html)
    try:
        ET.indent(arbol)
    except AttributeError:
        pass
    with open("InfoCircuito.html", "w", encoding="utf-8") as f:
        f.write("<!DOCTYPE html>\n")
        arbol.write(f, encoding="unicode")

    print("Archivo InfoCircuito.html generado correctamente.")

if __name__ == "__main__":
    main()
