import m from "mithril"
import List from "../models/List";

const FormGroup = {
    view(vnode) {
        return m('div.form-group',
            m('label.control-label', vnode.attrs.label),
            vnode.children
        );
    },
}

export default {
    view(vnode) {
        return m('form.form',
            m(FormGroup, {label: "Tickets #"},
                m('textarea.form-control', {cols:10, rows:20, placeholder: "un numéro par ligne"}, List.get().ids)
            ),
            m(FormGroup, {label: "Titre de la liste"},
                m('input.form-control', {placeholder: "nécessaire pour enregistrer la liste", value: List.get().name})
            ),
            m('button.btn.btn-primary', {title: "Enregistrer cette liste sur le serveur", disabled: (List.get().name === '')}, "Publier"),
        );
    },
}
