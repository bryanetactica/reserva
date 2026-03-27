document.addEventListener("DOMContentLoaded", () => {
    // Obtener el parámetro 'email' de la URL
    const email = new URLSearchParams(window.location.search).get("email");
    const standID = new URLSearchParams(window.location.search).get("standID");

    // Validar el email al cargar
    if (email) {
        document.getElementById('emailDisplay').innerText = `Email: ${email}`;
        // Llamar a la API para obtener los Estands ocupados
        fetchOccupiedSeats(email);
    } else {
        document.getElementById('emailDisplay').innerText = "No se ha proporcionado un email.";
    }

    // Lógica para manejar clic en los Estands
    const seats = document.querySelectorAll(".seats");
    seats.forEach((seat) => {
        seat.addEventListener("click", () => {
            const seatId = seat.id;
            // Verifica si el usuario ya tiene un Estand reservado
            if (seat.classList.contains("seats-user")) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Ya tienes un Estand reservado.' });
            } else {
                reserveSeat(email, seatId, standID);  // Reserva el Estand si no tiene uno
            }
        });
    });
});

// Función para obtener los Estands ocupados del servidor
async function fetchOccupiedSeats(email) {
    try {
	
	//Borrar
	//const response2 = await fetch("get_form.php");  // Cambia la URL a get_form.php
	//const data2 = await response2.json();

	//console.log(data2);


        const response = await fetch("get_occupied_seats.php");
        const data = await response.json();

        console.log(data); // Verifica qué datos están llegando

        if (data.success) {
            const occupiedSeats = data.occupiedSeats;

            // Marca los Estands ocupados y el Estand del usuario
            occupiedSeats.forEach((seat) => {
                const seatElement = document.getElementById(seat.seat_number);
                if (seatElement) {
                    if (seat.email === email) {
                        // Si el Estand es del usuario, cambiar el fondo a azul y desactivar clic
                        seatElement.style.backgroundColor = "rgb(29, 29, 204)"; // Color azul para el Estand del usuario
                        seatElement.style.color = "white";
                        seatElement.style.pointerEvents = "none"; // Desactiva clics
                        seatElement.style.cursor = "not-allowed"; // Cambia el cursor a "no permitido"
                        updateInput(seat.seat_number); // Enviar Estand ya reservado
                    } else {
                        // Si el Estand está ocupado por otro usuario, cambiar el fondo a rojo
                        seatElement.style.backgroundColor = "red"; // Color rojo para Estands ocupados
                        seatElement.style.color = "white";
                        seatElement.style.pointerEvents = "none"; // Desactiva clics
                        seatElement.style.cursor = "not-allowed"; // Cambia el cursor a "no permitido"
                    }
                }
            });

            // Si el usuario ya tiene un Estand, desactiva clic en todos los Estands
            if (data.userSeat) {
                await Swal.fire({ icon: 'info', title: 'Estand ya reservado', html: `Ya tienes reservado el Estand: <b>${data.userSeat}</b><br>Para reservar otro estand clicar en el botón verde <b>"restablecer selección"</b>` });
                disableAllSeats(); // Desactiva todos los Estands
                updateInput(data.userSeat); // Enviar el Estand ya reservado al documento principal
            }

            const emailExists = data.occupiedSeats.some(seat => seat.email === email);

            if (!emailExists) {
                updateInput("");
            }
        }
    } catch (error) {
        console.error("Error al cargar los Estands ocupados:", error);
    }
}

// Función para reservar un Estand
async function reserveSeat(email, seatId, standID) {

let errores = [];

if (!standID) errores.push("Debes seleccionar un Tipo de Estand");
if (!email) errores.push("o ingresar un email válido.");

if (errores.length > 0) {
    await Swal.fire({ icon: 'warning', title: 'Atención', html: errores.join('<br>') });
    return;
}


    try {
        const response = await fetch("reserve_seat.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email, seat_number: seatId, standID }),
        });

        const data = await response.json();

        if (data.success) {
            await Swal.fire({ icon: 'success', title: '¡Reserva Exitosa!', text: `Estand ${seatId} reservado correctamente.` });
            updateInput(seatId); // Enviar el Estand reservado al documento principal

            // Actualiza dinámicamente el estado del Estand reservado
            const seatElement = document.getElementById(seatId);
            seatElement.style.backgroundColor = "rgb(29, 29, 204)"; // Cambia el fondo al color azul
            seatElement.style.color = "white";
            seatElement.style.pointerEvents = "none"; // Desactiva clics
            seatElement.style.cursor = "not-allowed";


            iniciarContador(DURACION, timerElement);
        } else {
            fetchOccupiedSeats(email);
            await Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo reservar el Estand.' });
        }
    } catch (error) {
        console.error("Error al reservar el Estand:", error);
    }
}

// Función para desactivar todos los Estands
function disableAllSeats() {
    const seats = document.querySelectorAll(".seats");
    seats.forEach((seat) => {
        seat.style.pointerEvents = "none";
        seat.style.cursor = "not-allowed";
    });
}

// Función para enviar el Estand seleccionado al documento principal
function updateInput(seatId) {
    // Enviar mensaje al documento principal con el Estand seleccionado
    window.parent.postMessage(seatId, "*");
}

//
const DURACION = 300;
const timerElement = document.getElementById('timer');

function iniciarContador(duracion, elemento) {
    let tiempoRestante = duracion;

    const intervalo = setInterval(() => {
        // Calcula minutos y segundos
        const minutos = Math.floor(tiempoRestante / 60);
        const segundos = tiempoRestante % 60;

        // Formatea el tiempo con dos dígitos
        const tiempoFormateado = `${minutos}:${segundos < 10 ? '0' : ''}${segundos}`;

        // Actualiza el contenido del elemento
        elemento.textContent = tiempoFormateado;

        // Verifica si el contador llegó a cero
        if (tiempoRestante <= 0) {
            clearInterval(intervalo);
            elemento.textContent = "¡Reserva expirada!";

            // Llamar a la API para liberar el Estand y el email
            liberarReserva();
        }

        tiempoRestante--;
    }, 1000);
}

// Función para liberar la reserva en el servidor
async function liberarReserva() {
    const email = new URLSearchParams(window.location.search).get("email");

    if (!email) return; // Si no hay email, no hacer nada

    try {
        const response = await fetch("release_seat.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email }),
        });

        const data = await response.json();

        if (data.success) {
            updateInput("");
            await Swal.fire({ icon: 'success', title: 'Liberado', text: 'El Estand se ha liberado.' });
            location.reload(); // Recargar para actualizar el estado
        } else {
            console.error(data.message || "No se pudo liberar la reserva.");
        }
    } catch (error) {
        console.error("Error al liberar la reserva:", error);
    }
}

//
