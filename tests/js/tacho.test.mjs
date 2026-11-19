// node tests/js/tacho.test.mjs – testy licznika czasu pracy
import assert from 'node:assert/strict';
import { setActivity, compute, fmt, H, MIN } from '../../resources/js/tacho.js';

const T0 = new Date('2026-11-16T06:00:00').getTime(); // poniedziałek
const s = () => ({ events: [] });

// 1. 4 h jazdy → 30 min do przerwy
let st = s(); setActivity(st, 'drive', T0);
let c = compute(st, T0 + 4 * H);
assert.equal(fmt(c.toBreak), '0:30'); assert.ok(c.driving); assert.equal(c.limitBy, 'break');

// 2. przerwa 45 min zeruje jazdę ciągłą, nie dzienną
setActivity(st, 'rest', T0 + 4 * H); setActivity(st, 'drive', T0 + 4 * H + 45 * MIN);
c = compute(st, T0 + 5 * H + 45 * MIN);
assert.equal(fmt(c.continuous), '1:00'); assert.equal(fmt(c.daily), '5:00');

// 3. przerwa dzielona 15 + 30
st = s(); setActivity(st, 'drive', T0); setActivity(st, 'rest', T0 + 2 * H); setActivity(st, 'drive', T0 + 2 * H + 15 * MIN);
c = compute(st, T0 + 4 * H); assert.ok(c.splitFirst); assert.equal(fmt(c.continuous), '3:45');
setActivity(st, 'rest', T0 + 4 * H);
c = compute(st, T0 + 4 * H + 10 * MIN); assert.equal(fmt(c.breakInfo.need), '0:20');
setActivity(st, 'drive', T0 + 4 * H + 30 * MIN);
c = compute(st, T0 + 5 * H); assert.equal(fmt(c.continuous), '0:30');

// 4. przerwa 20 min bez wcześniejszej 15 nie zeruje (tylko zalicza jako pierwszą część)
st = s(); setActivity(st, 'drive', T0); setActivity(st, 'rest', T0 + 3 * H); setActivity(st, 'drive', T0 + 3 * H + 20 * MIN);
c = compute(st, T0 + 4 * H); assert.equal(fmt(c.continuous), '3:40');

// 5. odpoczynek 9 h zeruje dzień; limit dzienny 10 h przy dostępnych wydłużeniach
st = s(); setActivity(st, 'drive', T0); setActivity(st, 'rest', T0 + 9 * H); setActivity(st, 'drive', T0 + 18 * H);
c = compute(st, T0 + 19 * H); assert.equal(fmt(c.daily), '1:00'); assert.equal(fmt(c.dailyLimit), '10:00');
assert.equal(fmt(c.week), '10:00');

// 6. po dwóch dniach z jazdą > 9 h limit wraca do 9 h
st = s(); let t = T0;
for (let d = 0; d < 2; d++) { setActivity(st, 'drive', t); setActivity(st, 'rest', t + 9.5 * H); t += 24 * H; }
setActivity(st, 'drive', t);
c = compute(st, t + H); assert.equal(c.extUsed, 2); assert.equal(fmt(c.dailyLimit), '9:00');

// 7. limit tygodniowy 56 h
st = s(); t = T0;
for (let d = 0; d < 6; d++) { setActivity(st, 'drive', t); setActivity(st, 'rest', t + 9 * H); t += 24 * H; }
setActivity(st, 'drive', t);
c = compute(st, t + 30 * MIN); assert.equal(fmt(c.week), '54:30'); assert.equal(fmt(c.toWeek), '1:30');

// 8. sklejanie i cofanie w czasie
st = s(); setActivity(st, 'drive', T0); setActivity(st, 'drive', T0 + H); assert.equal(st.events.length, 1);
setActivity(st, 'rest', T0 + 2 * H); setActivity(st, 'drive', T0 + 2 * H); assert.equal(st.events.length, 1);

console.log('tacho: wszystkie testy OK');
