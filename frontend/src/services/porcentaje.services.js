import { Api } from './ApiService';

export default {
    listar: () => {
        return Api().get('porcentajes');
    }
};
