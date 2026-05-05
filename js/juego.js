"use strict";

class Juego {

    constructor() {
        this.preguntas = [
            {
                texto: "¿Cuál es el dulce típico de Girona relleno de crema?",
                opciones: ["Ensaimada", "Xuixo", "Crema catalana", "Panellet", "Churro"],
                correcta: 1
            },
            {
                texto: "¿Qué río atraviesa la ciudad de Girona?",
                opciones: ["Ebro", "Ter", "Segre", "Llobregat", "Onyar"],
                correcta: 4
            },
            {
                texto: "¿Qué tipo de ruta es el Camí de Ronda?",
                opciones: ["Gastronómica", "Ciclista", "Senderismo litoral", "Escalada", "Fluvial"],
                correcta: 2
            },
            {
                texto: "¿En qué siglo se construyó el Pont de les Peixateries Velles?",
                opciones: ["Siglo XV", "Siglo XVI", "Siglo XVII", "Siglo XIX", "Siglo XX"],
                correcta: 3
            },
            {
                texto: "¿Cuál es el punto más alto de la ruta del Carrilet I?",
                opciones: ["Olot", "Les Preses", "Amer", "Les Planes d'Hostoles", "Girona"],
                correcta: 0
            },
            {
                texto: "¿Qué plato típico es un guiso de pescado con patatas?",
                opciones: ["Arroz de Pals", "Xuixo", "Suquet de peix", "Crema catalana", "Escalivada"],
                correcta: 2
            },
            {
                texto: "¿Qué edificio románico de 1194 se conserva en Girona?",
                opciones: ["La Catedral", "Sant Feliu", "Banys Àrabs", "El Call", "Sant Pere de Galligants"],
                correcta: 2
            },
            {
                texto: "¿Dónde finaliza la ruta del Carrilet I?",
                opciones: ["Olot", "Amer", "Girona", "Les Preses", "Sant Feliu"],
                correcta: 2
            },
            {
                texto: "¿Cuál es el precio medio de las Anchoas de l'Escala?",
                opciones: ["2-3€", "5-7€", "8-15€", "18-25€", "30-40€"],
                correcta: 2
            },
            {
                texto: "¿Qué faro tiene vistas desde el Cap de Creus hasta el Montgrí?",
                opciones: ["Faro de Roses", "Faro de Palamós", "Faro de Sant Sebastià", "Faro de Tossa", "Faro de Cadaqués"],
                correcta: 2
            }
        ];

        this.respuestas = new Array(10).fill(-1);
        this.mostrarJuego();
    }

    mostrarJuego() {
        const main = document.querySelector("main");

        const seccion = document.createElement("section");

        const pIntro = document.createElement("p");
        pIntro.textContent = "Responde las 10 preguntas sobre Girona. Cada respuesta correcta vale 1 punto.";
        seccion.appendChild(pIntro);

        this.preguntas.forEach((pregunta, indice) => {
            const article = document.createElement("article");

            const h3 = document.createElement("h3");
            h3.textContent = (indice + 1) + ". " + pregunta.texto;
            article.appendChild(h3);

            pregunta.opciones.forEach((opcion, opIndice) => {
                const label = document.createElement("label");

                const radio = document.createElement("input");
                radio.type = "radio";
                radio.name = "pregunta" + indice;
                radio.value = opIndice;

                radio.addEventListener("change", () => {
                    this.respuestas[indice] = opIndice;
                });

                label.appendChild(radio);
                label.appendChild(document.createTextNode(" " + opcion));
                article.appendChild(label);

                article.appendChild(document.createElement("br"));
            });

            seccion.appendChild(article);
        });

        const boton = document.createElement("button");
        boton.textContent = "Finalizar juego";
        boton.addEventListener("click", () => this.corregir());
        seccion.appendChild(boton);

        main.appendChild(seccion);
    }

    corregir() {
        // Comprobar que todas están respondidas
        const sinResponder = this.respuestas.includes(-1);
        if (sinResponder) {
            alert("Debes responder todas las preguntas antes de finalizar.");
            return;
        }

        document.querySelector("button").disabled = true;
        document.querySelectorAll("input[type='radio']").forEach((radio) => {
            radio.disabled = true;
        });
        
        let puntuacion = 0;
        this.preguntas.forEach((pregunta, indice) => {
            if (this.respuestas[indice] === pregunta.correcta) {
                puntuacion++;
            }
        });

        const main = document.querySelector("main");
        const resultado = document.createElement("section");

        const h3 = document.createElement("h3");
        h3.textContent = "Resultado";
        resultado.appendChild(h3);

        const p = document.createElement("p");
        p.textContent = "Tu puntuación: " + puntuacion + " de 10";
        resultado.appendChild(p);

        main.appendChild(resultado);
    }
}