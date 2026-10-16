import Alpine from 'alpinejs';

/**
 * Generic repeater used by every "Add more" section.
 * rows: initial rows, blank: template for a new row.
 */
Alpine.data('repeater', (rows = [], blank = {}, startEmpty = false) => ({
    rows: rows.length ? rows.map((r, i) => ({ ...r, _k: i + 1 })) : (startEmpty ? [] : [{ ...blank, _k: 1 }]),
    _seq: rows.length + 1,
    add() { this.rows.push({ ...blank, _k: ++this._seq }); },
    remove(i) { this.rows.splice(i, 1); },
    money(v) { return (Math.round((Number(v) || 0) * 100) / 100).toFixed(2); },
    line(r, q = 'quantity', p = 'unit_price') { return (Number(r[q]) || 0) * (Number(r[p]) || 0); },
    sum(q = 'quantity', p = 'unit_price', filter = null) {
        return this.rows.filter(r => !filter || r.category === filter).reduce((s, r) => s + this.line(r, q, p), 0);
    },
}));

window.Alpine = Alpine;
Alpine.start();
