// Kanał 19 – licznik czasu jazdy i odpoczynku (orientacyjnie wg rozporządzenia (WE) 561/2006).
// Dane trzymane tylko w telefonie (localStorage). Wiążący jest zawsze tachograf.

export const MIN = 60 * 1000;
export const H = 60 * MIN;

export const LIMITS = {
    continuous: 4.5 * H,     // jazda bez przerwy
    breakFull: 45 * MIN,     // przerwa pełna
    breakFirst: 15 * MIN,    // przerwa dzielona: najpierw min. 15 min…
    breakSecond: 30 * MIN,   // …potem min. 30 min
    daily: 9 * H,            // dzienny czas jazdy
    dailyExtended: 10 * H,   // 2 razy w tygodniu
    extensionsPerWeek: 2,
    weekly: 56 * H,
    fortnight: 90 * H,
    dailyRest: 11 * H,
    dailyRestReduced: 9 * H,
};

const KEY = 'k19-tacho';

export function load(storage = globalThis.localStorage) {
    try {
        const s = JSON.parse(storage.getItem(KEY) || 'null');
        if (s && Array.isArray(s.events)) return s;
    } catch (e) { /* brak pamięci */ }
    return { events: [], auto: false };
}

export function save(state, storage = globalThis.localStorage) {
    try {
        // trzymamy ostatnie 15 dni
        const cut = Date.now() - 15 * 24 * H;
        state.events = state.events.filter(e => (e.end ?? Infinity) > cut);
        storage.setItem(KEY, JSON.stringify(state));
    } catch (e) { /* tryb prywatny */ }
}

/** Aktualna czynność: 'drive' albo 'rest' (domyślnie odpoczynek). */
export function current(state) {
    const last = state.events[state.events.length - 1];
    return last && last.end == null ? last : null;
}

/** Zmiana czynności od chwili `at` (można cofnąć w czasie, np. „10 minut temu”). */
export function setActivity(state, type, at = Date.now()) {
    const cur = current(state);
    if (cur && cur.type === type) return state;
    if (cur) {
        at = Math.max(at, cur.start);
        cur.end = at;
        if (cur.end - cur.start < 1000) state.events.pop();
    }
    const prev = state.events[state.events.length - 1];
    if (prev && prev.type === type && prev.end === at) { prev.end = null; return state; } // sklejamy
    state.events.push({ type, start: at, end: null });
    return state;
}

/** Odcinki jazdy/odpoczynku z domkniętym ostatnim w chwili `now`; przerwy między wpisami traktujemy jako odpoczynek. */
function segments(state, now) {
    const out = [];
    let t = null;
    for (const e of state.events) {
        if (t != null && e.start > t) out.push({ type: 'rest', start: t, end: e.start });
        const end = e.end ?? now;
        if (end > e.start) out.push({ type: e.type, start: e.start, end });
        t = end;
    }
    if (t != null && t < now) out.push({ type: 'rest', start: t, end: now });
    return out;
}

function mondayOf(ts) {
    const d = new Date(ts); const day = (d.getDay() + 6) % 7;
    d.setHours(0, 0, 0, 0); d.setDate(d.getDate() - day);
    return d.getTime();
}

/** Wszystkie liczniki w chwili `now`. */
export function compute(state, now = Date.now()) {
    const segs = segments(state, now);
    let continuous = 0, splitFirst = false;
    let daily = 0, dailyStart = null, lastDailyRestEnd = null;
    const dayTotals = []; // jazda w kolejnych okresach dziennych
    let lastRestLen = 0;

    for (const s of segs) {
        const len = s.end - s.start;
        if (s.type === 'drive') {
            if (dailyStart == null) dailyStart = s.start;
            continuous += len;
            daily += len;
            lastRestLen = 0;
        } else {
            lastRestLen = len;
            if (len >= LIMITS.breakFull || (splitFirst && len >= LIMITS.breakSecond)) { continuous = 0; splitFirst = false; }
            else if (len >= LIMITS.breakFirst) splitFirst = true;
            if (len >= LIMITS.dailyRestReduced) {
                if (daily > 0) dayTotals.push({ start: dailyStart, drive: daily });
                daily = 0; dailyStart = null; lastDailyRestEnd = s.end; continuous = 0; splitFirst = false;
            }
        }
    }

    const cur = current(state);
    const driving = cur?.type === 'drive';
    const weekStart = mondayOf(now);
    const prevWeekStart = weekStart - 7 * 24 * H;
    let week = 0, prevWeek = 0;
    for (const s of segs) {
        if (s.type !== 'drive') continue;
        const a = Math.max(s.start, weekStart), b = s.end;
        if (b > a) week += b - a;
        const pa = Math.max(s.start, prevWeekStart), pb = Math.min(s.end, weekStart);
        if (pb > pa) prevWeek += pb - pa;
    }

    // wydłużenia do 10 h w tym tygodniu (już wykorzystane)
    const extUsed = dayTotals.filter(d => d.start >= weekStart && d.drive > LIMITS.daily).length + (daily > LIMITS.daily && (dailyStart ?? now) >= weekStart ? 1 : 0);
    const dailyLimit = (extUsed < LIMITS.extensionsPerWeek || daily > LIMITS.daily) ? LIMITS.dailyExtended : LIMITS.daily;

    const toBreak = Math.max(0, LIMITS.continuous - continuous);
    const toDaily = Math.max(0, dailyLimit - daily);
    const toWeek = Math.max(0, Math.min(LIMITS.weekly - week, LIMITS.fortnight - week - prevWeek));
    const left = Math.min(toBreak, toDaily, toWeek);

    // przerwa w toku
    let breakInfo = null;
    if (!driving && cur) {
        const len = now - cur.start;
        const need = splitFirst && len < LIMITS.breakFirst ? LIMITS.breakSecond : (splitFirst ? LIMITS.breakSecond : LIMITS.breakFull);
        breakInfo = { elapsed: len, need: Math.max(0, need - len), split: splitFirst, daily: len >= LIMITS.dailyRestReduced };
    }

    // najpóźniejszy początek odpoczynku dziennego: 24 h od końca poprzedniego − 9 h (skrócony)
    const restBy = (lastDailyRestEnd ?? dailyStart) != null && daily > 0
        ? (lastDailyRestEnd ?? dailyStart) + 24 * H - LIMITS.dailyRestReduced : null;

    return {
        driving, since: cur?.start ?? null,
        continuous, toBreak, splitFirst,
        daily, dailyLimit, toDaily, extUsed,
        week, prevWeek, toWeek,
        left, limitBy: left === toBreak ? 'break' : left === toDaily ? 'daily' : 'week',
        breakInfo, restBy, lastRestLen,
    };
}

export function fmt(ms) {
    const m = Math.max(0, Math.round(ms / MIN));
    return Math.floor(m / 60) + ':' + String(m % 60).padStart(2, '0');
}

if (typeof window !== "undefined") window.K19Tacho = { load, save, current, setActivity, compute, fmt, LIMITS, H, MIN };
