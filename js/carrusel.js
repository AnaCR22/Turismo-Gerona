class Carrusel {

    constructor() {
        this.actual = 0;
        this.fotos = [
            { src: "multimedia/gerona-mapa.png", alt: "Mapa de situación de la provincia de Girona" },
            { src: "multimedia/gerona-paisaje.jpg", alt: "Paisaje de la Costa Brava" },
            { src: "multimedia/gerona-catedral.jpg", alt: "Catedral de Santa Maria de Girona" },
            { src: "multimedia/gerona-basilica.jpg", alt: "Basílica de Sant Feliu" },
            { src: "multimedia/gerona-monasterio.jpg", alt: "Monasterio de Sant Pere de Galligants" }
        ];    
    }

   
    //uso de jquery 
    mostrarFotografias() {
        const $main = $("main");
        const $articulo = $("<article>").appendTo($main);
        $("<h2>").text("Imágenes de Gerona").appendTo($articulo);

        $("<img>")
            .attr("src", this.fotos[0].src)
            .attr("alt", this.fotos[0].alt)
            .appendTo($articulo);

        setInterval(this.cambiarFotografia.bind(this), 3000);
    }

    cambiarFotografia() {
        this.actual++;
        if (this.actual > this.fotos.length - 1) {
            this.actual = 0;
        }

        $("main article img")
            .attr("src", this.fotos[this.actual].src)
            .attr("alt", this.fotos[this.actual].alt);
    }
}

