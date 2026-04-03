"use strict";

class Ciudad {
    constructor(nombre, gentilicio) {
        this.nombre = nombre;
        this.gentilicio = gentilicio;
        this.poblacion = 0;
        this.lat = 0;
        this.lon = 0;
    }

    rellenarDatos(poblacion, lat, lon) {
        this.poblacion = poblacion;
        this.lat = lat;
        this.lon = lon;
    }

    getNombre() {
        return "Nombre de la capital: " + this.nombre;
    }

    getInfoSecundaria() {
        return "<ul>" +
                "<li>Gentilicio: " + this.gentilicio + "</li>" +
                "<li>Población: " + this.poblacion + "</li>" +
                "</ul>";
    }

    escribirCoordenadas() {
        const $p = $("<p></p>").text("Coordenadas: " + this.lat + ", " + this.lon);
        this.$main.append($p);
    }

    getMeteorologiaCiudad() {
        $.ajax({
            url: "https://api.open-meteo.com/v1/forecast",
            data: {
                latitude: this.lat,
                longitude: this.lon,
                current: "temperature_2m,relative_humidity_2m,apparent_temperature,weather_code,wind_speed_10m",
                daily: "weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,wind_speed_10m_max",
                timezone: "auto"
            },
            dataType: "json",
            method: "GET",
            success: (json) => this.procesarDatos(json),
            error: () => console.error("Error al obtener los datos meteorológicos")
        });
    }

    procesarDatos(json) {
        if (!json || !json.current || !json.daily) {
            console.error("No se pudo cargar la información meteorológica");
            return;
        }

        this.actual = json.current;
        this.prevision = json.daily;

        this.mostrarTiempoActual();
        this.mostrarPrevision();
    }

    mostrarTiempoActual() {
        const $main = $("main");
        const $seccion = $("<section>").appendTo($main);
        $("<h3>").text("Tiempo actual en " + this.nombre).appendTo($seccion);

        const $tiempoActual = $("<ul>").appendTo($seccion);;
        $("<li>").text("Temperatura: " + this.actual.temperature_2m + "°C").appendTo($tiempoActual);
        $("<li>").text("Sensación térmica: " + this.actual.apparent_temperature + "°C").appendTo($tiempoActual);
        $("<li>").text("Humedad: " + this.actual.relative_humidity_2m + "%").appendTo($tiempoActual);
        $("<li>").text("Viento: " + this.actual.wind_speed_10m + "km/h").appendTo($tiempoActual);

    }

    mostrarPrevision() {
        const main = document.querySelector("main");
        if (!main) {
            console.error("No se encontró el elemento main");
            return;
        }

        const $seccion = $("<section>").appendTo(main);

        $("<h3>").text("Previsión para los próximos 7 días").appendTo($seccion);

        const $listaDias = $("<ul>").appendTo($seccion);;

        for (let i = 0; i < this.prevision.time.length; i++) {
            const $dia = $("<li>").appendTo($listaDias);
            $("<strong>").text(this.prevision.time[i]).appendTo($dia);

            const $previsionDia = $("<ul>").appendTo($dia);

            $("<li>").text("Máxima: " + this.prevision.temperature_2m_max[i] + "°C").appendTo($previsionDia);
            $("<li>").text("Mínima: " + this.prevision.temperature_2m_min[i] + "°C").appendTo($previsionDia);
            $("<li>").text("Probabilidad de precipitación: " + this.prevision.precipitation_probability_max[i] + "%").appendTo($previsionDia);
            $("<li>").text("Viento máximo: " + this.prevision.wind_speed_10m_max[i] + "km/h").appendTo($previsionDia);
        }
    }
}