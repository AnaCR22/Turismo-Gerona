class Carrusel {

    constructor() {
        this.actual = 0;
        this.fotos = [
            "multimedia/gerona-mapa.png",
            "multimedia/gerona-paisaje.jpg",
            "multimedia/gerona-catedral.jpg",
            "multimedia/gerona-basilica.jpg",
            "multimedia/gerona-monasterio.jpg"
        ];    
    }

   

    mostrarFotografias() {
        const $main = $("main");
        const $articulo = $("<article>").appendTo($main);
        $("<h2>").text("Imágenes de Gerona").appendTo($articulo);

        $("<img>").attr("src", this.fotos[0]).appendTo($articulo);

        setInterval(this.cambiarFotografia.bind(this), 3000);
    }

    cambiarFotografia() {

        this.actual++;

        if (this.actual > this.fotos.length - 1) {
            this.actual = 0;
        }

        $("main img").attr("src", this.fotos[this.actual]);
    }
}

