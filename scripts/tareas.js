document.addEventListener("DOMContentLoaded", function () {
  loadTasks();

  document.getElementById("formTarea").addEventListener("submit", function (e) {
    e.preventDefault();
    const name = document.getElementById("nombreTarea").value;
    createTask(name);
  });
});



const descargarReporte =  document.getElementById("reporteTarea");
descargarReporte.addEventListener("click", function () {
    window.location.href = "./resources/script.php";    
})

function createTask(name) {
  if (name.trim() === "") {
    Notiflix.Report.warning(
      "Atención!",
      "No puedes agregar una tarea vacía. Por favor, ingresa un título para la tarea.",
      "Entiendo",
      function () {
        document.getElementById("nombreTarea").focus();
      }
    );
  } else {
    fetch("./controllers/tareas.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `action=create&nombre=${encodeURIComponent(name.trim())}`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.message) {
          Notiflix.Report.success(
            "Éxito",
            data.message,
            "Entendido",
            function () {
              loadTasks();
              document.getElementById("nombreTarea").value = "";
            }
          );
        } else if (data.error) {
          Notiflix.Report.failure(
            "Error",
            data.error,
            "Entendido",
            function () {
              document.getElementById("nombreTarea").focus();
            }
          );
        }
      })
      .catch((error) => {
        Notiflix.Report.failure(
          "Error",
          "Hubo un problema con la solicitud. Inténtalo de nuevo.",
          "Entendido"
        );
      });
  }
}


function loadTasks() {
  Notiflix.Loading.standard('Cargando tareas...');

  fetch("./controllers/tareas.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error('No se pudo obtener las tareas');
      }
      return response.json();
    })
    .then((tareas) => {
      const tbody = document.getElementById("tareasTable");
      tbody.innerHTML = "";

      if (tareas.length === 0) {
        Notiflix.Notify.info("No hay tareas añadidas.");
      } else {
        tareas.forEach((tarea) => {
          tbody.innerHTML += `
                <tr>
                    <td>${tarea.name}</td>
                    <td>${tarea.creation_date	}</td>
                    <td>
                        <select class="form-select" onchange="updateStatus(${tarea.id}, this.value)">
                            <option value="1" ${
                              tarea.estado	 === "Pendiente" ? "selected" : ""
                            }>Pendiente</option>
                            <option value="2" ${
                              tarea.estado	 === "Realizada" ? "selected" : ""
                            }>Realizada</option>
                            <option value="3" ${
                              tarea.estado	 === "Cancelada" ? "selected" : ""
                            }>Cancelada</option>
                        </select>
                    </td>
                    <td><button class="btn btn-danger" onclick="deleteTask(${tarea.id})">Eliminar</button></td>
                </tr>`;
        });
        Notiflix.Notify.success("Tareas cargadas correctamente.");
      }
    })
    .catch((error) => {
      Notiflix.Notify.failure("Error al cargar las tareas. Inténtalo de nuevo.");
      console.error(error);
    })
    .finally(() => {
      Notiflix.Loading.remove();
    });
}

function updateStatus(id, status_id) {
  Notiflix.Loading.standard('Actualizando estado...');

  fetch("./controllers/tareas.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=update&id=${id}&estado_id=${status_id }`,
  })
    .then((response) => response.json())
    .then((data) => {

      Notiflix.Loading.remove();

      loadTasks();

      if (data.success) {
        Notiflix.Notify.success(data.message);
      } else {
        Notiflix.Notify.failure(data.message);
      }
    })
    .catch((error) => {
      Notiflix.Loading.remove();
      Notiflix.Notify.failure('Error al actualizar el estado. Intenta de nuevo.');
      console.error('Error:', error);
    });
}


function deleteTask(id) {
  Notiflix.Confirm.show(
    "Atención!",
    "¿Estás seguro de eliminar esta tarea?",
    "Sí, estoy seguro",
    "No, Cancelar",
    function () {
      fetch("./controllers/tareas.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=delete&id=${id}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            Notiflix.Report.success("Éxito", data.message, "Entiendo");
          } else {
            Notiflix.Report.failure("Error", data.message, "Entiendo");
          }
          loadTasks();
        })
        .catch((error) => {
          Notiflix.Report.failure("Error", "Ocurrió un problema al intentar eliminar la tarea.", "Entiendo");
        });
    },
    function () {
      Notiflix.Report.info("Atención!", "Tarea no eliminada", "Entiendo");
    }
  );
}
