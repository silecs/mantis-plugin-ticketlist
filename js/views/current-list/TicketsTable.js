import m from "mithril"

const TicketRow = {
    view(vnode) {
        const ticket = vnode.attrs.ticket
        const selectable = vnode.attrs.selectable
        return m('tr', {class: `status-${ticket.status}-bg`},
            m('td', (selectable instanceof Array) ? m(IssueCheckbox, {id: ticket.id, selectable}) : null),
            m('td', m.trust(ticket.link)),
            m('td', ticket.statusText),
            m('td', ticket.summary),
        );
    },
}

const IssueCheckbox = {
    view(vnode) {
        const selectable = vnode.attrs.selectable;
        return m('input', {
            class: 'selected-issues',
            type: "checkbox",
            name: "issueId",
            value: vnode.attrs.id,
            onchange(event) {
                selectable.length = 0
                const inputs = event.target.closest('tbody').querySelectorAll('input.selected-issues:checked')
                for (const input of inputs) {
                    const id = parseInt(input.value, 10)
                    if (!isNaN(id) && id > 0) {
                        selectable.push(id)
                    }
                }
            }
        });
    },
}

export default {
    view(vnode) {
        const tickets = vnode.attrs.tickets;
        const selectable = vnode.attrs.selectable ?? false;
        return m('table', {class: "buglist table table-bordered table-condensed table-hover"},
            m('thead',
                m('tr',
                    (selectable instanceof Array) ? m('th') : null,
                    m('th', "ID"),
                    m('th', "statut"),
                    m('th', "résumé"),
                ),
            ),
            m('tbody',
                tickets.map((ticket) => m(TicketRow, {ticket, selectable, key: ticket.id}))
            )
        );
    },
}
