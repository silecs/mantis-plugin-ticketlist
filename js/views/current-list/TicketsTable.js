import m from "mithril"

const TicketRow = {
    view(vnode) {
        const ticket = vnode.attrs.ticket
        const selectable = vnode.attrs.selectable;
        return m('tr', {class: `status-${ticket.status}-bg`},
            selectable ? m('td', m('input', {type: "checkbox", name: "ids", value: ticket.id})) : null,
            m('td', m.trust(ticket.link)),
            m('td', ticket.statusText),
            m('td', ticket.summary),
        );
    },
}

export default {
    view(vnode) {
        const tickets = vnode.attrs.tickets;
        const selectable = vnode.attrs.selectable ?? false;
        return m('table', {class: "buglist table table-bordered table-condensed table-hover"},
            m('thead',
                m('tr',
                    selectable ? m('th') : null,
                    m('th', "ID"),
                    m('th', "statut"),
                    m('th', "résumé"),
                ),
            ),
            m('tbody',
                tickets.map((ticket) => m(TicketRow, {ticket, selectable}))
            )
        );
    },
}
