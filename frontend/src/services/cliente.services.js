import { Api } from "./ApiService";


export default {
    // Obtener todos los clientes (personas e instituciones)
    index: () => {
        return Api().get('cliente');
    },

    // Crear un cliente o institución (uso general)
    store: (cliente) => {
        return Api().post('cliente', cliente);
    },

    /**
     * Crea un registro desde el Wizard de cotización.
     * Mapea automáticamente los campos del formulario al esquema de la BD.
     *
     * @param {object} datos - { nombre, nit, email, telefono, tipo, cliente_id? }
     *   tipo: 'institucion' → tipo_cliente_id=1
     *   tipo: 'cliente'     → tipo_cliente_id=2 (Persona de contacto)
     * @returns {Promise} con el cliente creado (incluye .id real)
     */
    crearDesdeWizard: (datos) => {
        return Api().post('cliente', {
            nombre:          datos.nombre,
            ci_nit:          datos.nit      || null,
            correo:          datos.email    || null,  // el backend usa 'correo', el formulario usa 'email'
            telefono:        datos.telefono || null,
            tipo_cliente_id: datos.tipo === 'institucion' ? 1 : 2,
            cliente_id:      datos.cliente_id || null, // FK → institución (solo para contactos)
        });
    },

    show: (id) => {
        return Api().get(`cliente/${id}`);
    },
    update: (id, cliente) => {
        return Api().put(`cliente/${id}`, cliente);
    },
    destroy: (id) => {
        return Api().delete(`cliente/${id}`);
    },
    listarInstituciones: () => {
        return Api().get('cliente/instituciones');
    },
    listarContactos: () => {
        return Api().get(`cliente/contactos`);
    },
    listarContactosPorInstitucion: (institucion_id) => {
        return Api().get(`cliente/instituciones/${institucion_id}/contactos`);
    }
}

