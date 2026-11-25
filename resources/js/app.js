const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

const IS_CYRILLIC = document.documentElement?.dataset?.script === 'cyrl';

const uzToCyrl = (text) => {
    if (!text || typeof text !== 'string' || !/[A-Za-z]/.test(text)) return text || '';
    const keep = ['Yubor Market', 'YUBOR', 'MARKET', 'Telegram', 'WhatsApp', 'Email', 'SMS', 'SBP', 'MIR', 'EMU', 'RUB', 'UZS', 'VK'];
    const saved = [];
    let s = text;
    keep.forEach((tok) => {
        const re = new RegExp(`(?<![A-Za-z])${tok.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}(?![A-Za-z])`, 'g');
        s = s.replace(re, (m) => {
            const id = `\x01${saved.length}\x02`;
            saved.push(m);
            return id;
        });
    });
    s = s
        .replace(/O['‘’ʼ`]/g, 'Ў').replace(/o['‘’ʼ`]/g, 'ў')
        .replace(/G['‘’ʼ`]/g, 'Ғ').replace(/g['‘’ʼ`]/g, 'ғ')
        .replace(/Yoў|YOў|Yў/g, 'Йў').replace(/yoў|yў/g, 'йў')
        .replace(/SH|Sh/g, 'Ш').replace(/sh/g, 'ш')
        .replace(/CH|Ch/g, 'Ч').replace(/ch/g, 'ч')
        .replace(/YO|Yo/g, 'Ё').replace(/yo/g, 'ё')
        .replace(/YA|Ya/g, 'Я').replace(/ya/g, 'я')
        .replace(/YU|Yu/g, 'Ю').replace(/yu/g, 'ю')
        .replace(/(^|[\s(\["«—\-]|[aeiouўаеёиоуўэюяAEIOUЎАЕЁИОУЎЭЮЯ])Ye/g, '$1Е')
        .replace(/(^|[\s(\["«—\-]|[aeiouўаеёиоуўэюяAEIOUЎАЕЁИОУЎЭЮЯ])ye/g, '$1е')
        .replace(/(?<![A-Za-zА-Яа-яЎўҚқҒғҲҳ])E/g, 'Э')
        .replace(/(?<![A-Za-zА-Яа-яЎўҚқҒғҲҳ])e/g, 'э')
        .replace(/(?<=[aeiouўаеёиоуўэюяAEIOUЎАЕЁИОУЎЭЮЯʼ’'])e/g, 'э')
        .replace(/(?<=[A-Za-zА-Яа-яЎўҚқҒғҲҳ])['‘’ʼ`](?=[A-Za-zА-Яа-яЎўҚқҒғҲҳ])/g, 'ъ');
    const map = {
        A:'А',a:'а',B:'Б',b:'б',D:'Д',d:'д',E:'Е',e:'е',F:'Ф',f:'ф',G:'Г',g:'г',H:'Ҳ',h:'ҳ',
        I:'И',i:'и',J:'Ж',j:'ж',K:'К',k:'к',L:'Л',l:'л',M:'М',m:'м',N:'Н',n:'н',O:'О',o:'о',
        P:'П',p:'п',Q:'Қ',q:'қ',R:'Р',r:'р',S:'С',s:'с',T:'Т',t:'т',U:'У',u:'у',V:'В',v:'в',
        X:'Х',x:'х',Y:'Й',y:'й',Z:'З',z:'з',
    };
    s = s.replace(/[A-Za-z]/g, (ch) => map[ch] || ch);
    saved.forEach((tok, i) => { s = s.replace(`\x01${i}\x02`, tok); });
    return s;
};

const t = (text) => (IS_CYRILLIC ? uzToCyrl(text) : text);

const STORE_KEY = 'yubor_checkout';
const ORDER_KEY = 'yubor_active_order';

const store = {
    get() {
        try { return JSON.parse(localStorage.getItem(STORE_KEY)) || {}; } catch { return {}; }
    },
    set(data) {
        try { localStorage.setItem(STORE_KEY, JSON.stringify({ ...store.get(), ...data })); } catch {}
    },
};

/* ---------- Slayder yordamchilari ---------- */
// Slaydlar orasida gap bo'lishi mumkin — qadamni haqiqiy joylashuvdan hisoblaymiz
const slideStep = (track) => {
    const [a, b] = track.children;
    return a && b ? b.offsetLeft - a.offsetLeft : track.clientWidth;
};
const slideIndex = (track) => Math.round(track.scrollLeft / slideStep(track));
const goToSlide = (track, index, smooth = true) => {
    const max = track.children.length - 1;
    const i = Math.max(0, Math.min(max, index));
    track.scrollTo({ left: i * slideStep(track), behavior: smooth ? 'smooth' : 'instant' });
};

// Telefonda barmoq bilan surish native scroll-snap orqali ishlaydi.
// Kompyuterda sichqoncha bilan sudrab o'tkazish uchun:
function enableMouseSwipe(track) {
    let startX = null;
    let dragged = false;
    track.addEventListener('pointerdown', (e) => {
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
        startX = e.clientX;
        dragged = false;
    });
    track.addEventListener('pointermove', (e) => {
        if (startX !== null && Math.abs(e.clientX - startX) > 6) dragged = true;
    });
    const end = (e) => {
        if (startX === null) return;
        const dx = e.clientX - startX;
        startX = null;
        if (Math.abs(dx) > 40) goToSlide(track, slideIndex(track) + (dx < 0 ? 1 : -1));
    };
    track.addEventListener('pointerup', end);
    track.addEventListener('pointerleave', end);
    // sudrashdan keyin "click" ishlamasin
    track.addEventListener('click', (e) => {
        if (dragged) { e.stopPropagation(); e.preventDefault(); dragged = false; }
    }, true);
    track.addEventListener('dragstart', (e) => e.preventDefault());
}

function onSlideChange(track, cb) {
    let last = -1;
    const handler = () => {
        const i = slideIndex(track);
        if (i !== last) { last = i; cb(i); }
    };
    track.addEventListener('scroll', handler, { passive: true });
    return handler;
}

/* ---------- Tovar galereyasi (swipe + nuqtalar + to'liq ekran) ---------- */
function initGallery() {
    const gallery = $('#gallery');
    if (!gallery) return;
    const dots = $$('[data-gallery-dots] > span');
    const counter = $('[data-gallery-index]');

    enableMouseSwipe(gallery);
    onSlideChange(gallery, (index) => {
        if (counter) counter.textContent = index + 1;
        dots.forEach((dot, i) => {
            const active = i === index;
            dot.classList.toggle('w-[16px]', active);
            dot.classList.toggle('bg-primary', active);
            dot.classList.toggle('w-[6px]', !active);
            dot.classList.toggle('bg-[#c8d3df]', !active);
        });
    });


}

function initViewer() {
    const gallery = $('#gallery'); // tovar sahifasida bo'ladi, boshqa sahifalarda null
    const viewer = $('#viewer');
    if (!viewer) return;
    const track = $('[data-viewer-track]', viewer);
    const counter = $('[data-viewer-index]', viewer);
    const thumbs = $$('[data-viewer-thumb]', viewer);
    const thumbsBox = $('[data-viewer-thumbs]', viewer);

    const resetZoom = () => $$('[data-zoom]', viewer).forEach((img) => {
        img.style.transform = '';
        img.classList.replace('cursor-zoom-out', 'cursor-zoom-in');
    });

    const setActive = (index) => {
        counter.textContent = index + 1;
        thumbs.forEach((t, i) => (t.dataset.active = String(i === index)));
        const thumb = thumbs[index];
        if (thumb) {
            thumbsBox.scrollTo({
                left: thumb.offsetLeft - thumbsBox.clientWidth / 2 + thumb.clientWidth / 2,
                behavior: 'smooth',
            });
        }
        resetZoom();
    };

    enableMouseSwipe(track);
    onSlideChange(track, setActive);

    const open = (index) => {
        viewer.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        goToSlide(track, index, false);
        setActive(index);
    };
    const close = () => {
        const index = slideIndex(track);
        viewer.classList.add('hidden');
        document.body.style.overflow = '';
        if (gallery) goToSlide(gallery, index, false);
    };

    $$('[data-open-viewer]').forEach((btn) =>
        btn.addEventListener('click', () => open(Number(btn.dataset.openViewer))));
    $('[data-viewer-close]', viewer).addEventListener('click', close);
    const go = (index) => {
        const i = Math.max(0, Math.min(thumbs.length - 1, index));
        goToSlide(track, i);
        setActive(i);
    };
    thumbs.forEach((t) => t.addEventListener('click', () => go(Number(t.dataset.viewerThumb))));

    // Rasmni bosib kattalashtirish (zoom) — yana bosilsa qaytadi
    $$('[data-zoom]', viewer).forEach((img) => img.addEventListener('click', (e) => {
        if (img.style.transform) { resetZoom(); return; }
        const r = img.getBoundingClientRect();
        img.style.transformOrigin = `${e.clientX - r.left}px ${e.clientY - r.top}px`;
        img.style.transform = 'scale(2)';
        img.classList.replace('cursor-zoom-in', 'cursor-zoom-out');
    }));

    document.addEventListener('keydown', (e) => {
        if (viewer.classList.contains('hidden')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowRight') go(slideIndex(track) + 1);
        if (e.key === 'ArrowLeft') go(slideIndex(track) - 1);
    });
}

/* ---------- Bosh sahifa banner karuseli ---------- */
// Har `interval` ms da keyingi bannerga o'tadi, oxiridan yana boshiga qaytadi.
// Foydalanuvchi surayotganda / sichqoncha ustida turganda / tab yashirin bo'lganda to'xtaydi.
function initBannerCarousel() {
    $$('[data-carousel]').forEach((carousel) => {
        const track = $('[data-carousel-track]', carousel);
        const dots = $$('[data-carousel-dots] > span', carousel);
        const total = track.children.length;
        if (total < 2) return;

        const interval = Number(carousel.dataset.interval) || 4000;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let timer = null;
        let resumeTimer = null;

        const setDots = (index) => dots.forEach((dot, i) => {
            dot.classList.toggle('w-[14px]', i === index);
            dot.classList.toggle('w-[4px]', i !== index);
            dot.classList.toggle('opacity-50', i !== index);
        });

        const next = () => {
            const i = (slideIndex(track) + 1) % total;
            goToSlide(track, i, !reduceMotion);
            setDots(i);
        };
        const start = () => { stop(); timer = setInterval(next, interval); };
        const stop = () => { clearInterval(timer); timer = null; };
        const pauseThenResume = () => {
            stop();
            clearTimeout(resumeTimer);
            resumeTimer = setTimeout(start, interval);
        };

        enableMouseSwipe(track);
        onSlideChange(track, setDots);

        // foydalanuvchi o'zi surganda avtomatik aylanish vaqtincha to'xtaydi
        track.addEventListener('pointerdown', stop);
        track.addEventListener('touchstart', stop, { passive: true });
        track.addEventListener('pointerup', pauseThenResume);
        track.addEventListener('touchend', pauseThenResume, { passive: true });
        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', pauseThenResume);
        document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));

        start();
    });
}

/* ---------- Ulashish ---------- */
// 1) Tizimning o'z ulashish oynasi (iOS/Android share sheet) — navigator.share orqali.
// 2) U bo'lmasa (kompyuter brauzeri, http sahifa) — o'zimizning bottom sheet (#share-sheet).
function initShare() {
    const sheet = $('#share-sheet');
    const buttons = $$('[data-share]');
    if (!sheet || !buttons.length) return;

    const url = location.origin + location.pathname;
    const title = sheet.dataset.shareTitle || document.title;
    const text = sheet.dataset.shareText || title;
    const data = { title, text, url };

    // Ilova havolalari
    const enc = encodeURIComponent;
    const links = {
        telegram: `https://t.me/share/url?url=${enc(url)}&text=${enc(text)}`,
        whatsapp: `https://wa.me/?text=${enc(`${text} ${url}`)}`,
        vk: `https://vk.com/share.php?url=${enc(url)}&title=${enc(title)}`,
        sms: `sms:?&body=${enc(`${text} ${url}`)}`,
        email: `mailto:?subject=${enc(title)}&body=${enc(`${text}\n${url}`)}`,
    };
    $$('[data-share-app]', sheet).forEach((a) => {
        a.href = links[a.dataset.shareApp];
        if (/^(sms|mailto):/.test(a.href)) a.removeAttribute('target');
        a.addEventListener('click', () => setTimeout(() => closeSheet(sheet), 150));
    });
    $('[data-share-url]', sheet).textContent = url.replace(/^https?:\/\//, '');

    const copyBtn = $('[data-share-copy]', sheet);
    copyBtn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(url);
        } catch {
            // eski brauzerlar / http uchun zaxira usul
            const tmp = Object.assign(document.createElement('textarea'), { value: url });
            document.body.append(tmp);
            tmp.select();
            document.execCommand('copy');
            tmp.remove();
        }
        copyBtn.dataset.copied = 'true';
        copyBtn.textContent = t('Nusxalandi ✓');
        setTimeout(() => { copyBtn.dataset.copied = 'false'; copyBtn.textContent = t('Nusxalash'); }, 1800);
    });

    $$('[data-close-sheet]', sheet).forEach((el) => el.addEventListener('click', () => closeSheet(sheet)));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && sheet.dataset.open === 'true') closeSheet(sheet);
    });

    buttons.forEach((btn) => btn.addEventListener('click', async () => {
        const canNative = typeof navigator.share === 'function' && (!navigator.canShare || navigator.canShare(data));
        if (canNative) {
            try {
                await navigator.share(data);
                return;
            } catch (err) {
                if (err?.name === 'AbortError') return; // foydalanuvchi o'zi yopdi
                // boshqa xato bo'lsa — o'zimizning oynani ko'rsatamiz
            }
        }
        openSheet(sheet);
    }));
}

/* ---------- "Ko'proq ko'rish" (bosh sahifa bo'limlari) ---------- */
function initLoadMore() {
    $$('[data-load-more]').forEach((btn) => {
        const section = btn.closest('[data-load-more-section]');
        const step = Number(btn.dataset.loadMore) || 4;
        btn.addEventListener('click', () => {
            $$('[data-more-item].hidden', section).slice(0, step).forEach((item) => item.classList.remove('hidden'));
            // hammasi ochilgan bo'lsa tugma yashiriladi
            if (!$('[data-more-item].hidden', section)) btn.remove();
        });
    });
}

/* ---------- "Batafsil" ---------- */
function initCollapsible() {
    $$('[data-collapsible]').forEach((box) => {
        const text = $('[data-collapsible-text]', box);
        const btn = $('[data-collapsible-toggle]', box);
        btn?.addEventListener('click', () => {
            const open = text.classList.toggle('line-clamp-4');
            btn.textContent = open ? t('Batafsil') : t('Yopish');
        });
    });
}

/* ---------- Telefon maskasi ---------- */
// Prefiks (+7 / +998) inputdan tashqarida doimiy turadi, input faqat qolgan raqamlarni oladi.
// To'liq raqam qo'yilsa (+7..., 8..., +998...) davlat kodi avtomatik olib tashlanadi.
function formatPhone(value, type) {
    let digits = value.replace(/\D/g, '');
    if (type === 'ru') {
        if (digits.length > 10 && /^[78]/.test(digits)) digits = digits.slice(1);
        const d = digits.slice(0, 10);
        const p = [d.slice(0, 3), d.slice(3, 6), d.slice(6, 8), d.slice(8, 10)];
        return (p[0] + (p[1] ? ' ' + p[1] : '') + (p[2] ? ' - ' + p[2] : '') + (p[3] ? ' - ' + p[3] : '')).trim();
    }
    if (digits.length > 9 && digits.startsWith('998')) digits = digits.slice(3);
    const d = digits.slice(0, 9);
    const p = [d.slice(0, 2), d.slice(2, 5), d.slice(5, 7), d.slice(7, 9)];
    return (p[0] + (p[1] ? ' ' + p[1] : '') + (p[2] ? ' - ' + p[2] : '') + (p[3] ? ' - ' + p[3] : '')).trim();
}

// Mo'ljal kabi ko'p qatorli maydonlar matn ko'paygan sari pastga cho'ziladi
function initAutogrow() {
    $$('textarea[data-autogrow]').forEach((ta) => {
        const fit = () => { ta.style.height = 'auto'; ta.style.height = ta.scrollHeight + 'px'; };
        ta.addEventListener('input', fit);
        fit();
    });
}

function initPhoneMasks() {
    $$('input[data-mask]').forEach((input) => {
        input.addEventListener('input', () => {
            input.value = formatPhone(input.value, input.dataset.mask);
        });
    });
}

/* ---------- Bottom sheet ---------- */
function openSheet(sheet) {
    sheet.dataset.open = 'true';
    sheet.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    const search = $('[data-sheet-search]', sheet);
    if (search) { search.value = ''; filterSheet(sheet, ''); }
    // tanlangan elementni ko'rinadigan joyga olib kelish
    $('[data-selected="true"]', sheet)?.scrollIntoView({ block: 'center' });
}

function closeSheet(sheet) {
    sheet.dataset.open = 'false';
    sheet.setAttribute('aria-hidden', 'true');
    // to'liq ekranli galereya ochiq bo'lsa, sahifa scroll'i qulflangan qolsin
    const viewerOpen = document.getElementById('viewer') && !document.getElementById('viewer').classList.contains('hidden');
    if (!viewerOpen) document.body.style.overflow = '';
}

function filterSheet(sheet, query) {
    const q = query.trim().toLowerCase();
    $$('[data-sheet-option]', sheet).forEach((btn) => {
        const raw = btn.dataset.sheetOption.toLowerCase();
        const cyrl = uzToCyrl(btn.dataset.sheetOption).toLowerCase();
        btn.parentElement.hidden = q && !raw.includes(q) && !cyrl.includes(q);
    });
}

function renderOptions(sheet, items) {
    const list = $('[data-sheet-list]', sheet);
    list.innerHTML = items.map((item) => `
        <li>
            <button type="button" data-sheet-option="${item}"
                class="group flex w-full items-center justify-between border-b border-divider py-[13px] text-left">
                <span class="text-[15px] leading-normal whitespace-nowrap text-ink group-data-[selected=true]:font-semibold">${t(item)}</span>
                <img src="/img/icon-check.svg" alt="" class="hidden size-[22px] group-data-[selected=true]:block">
            </button>
        </li>`).join('');
}

function initCheckout() {
    const form = $('#checkout-form');
    if (!form) return;

    const regionSheet = $('#region-sheet');
    const districtSheet = $('#district-sheet');
    const districts = window.__DISTRICTS__ || {};
    const state = { region: null, district: null, pending: null };

    const setField = (name, value, placeholder) => {
        const btn = $(`[data-value-for="${name}"]`).closest('button');
        $(`[data-value-for="${name}"]`).textContent = t(value || placeholder);
        $(`input[name="${name}"]`, btn).value = value || '';
        btn.dataset.filled = value ? 'true' : 'false';
    };

    const showDelivery = (region) => {
        const note = $('#delivery-note');
        note.classList.toggle('hidden', !region);
        note.classList.toggle('flex', !!region);
    };

    [regionSheet, districtSheet].forEach((sheet) => {
        $$('[data-close-sheet]', sheet).forEach((el) => el.addEventListener('click', () => closeSheet(sheet)));
        $('[data-sheet-search]', sheet)?.addEventListener('input', (e) => filterSheet(sheet, e.target.value));
        $('[data-sheet-list]', sheet).addEventListener('click', (e) => {
            const btn = e.target.closest('[data-sheet-option]');
            if (!btn) return;
            $$('[data-sheet-option]', sheet).forEach((b) => (b.dataset.selected = 'false'));
            btn.dataset.selected = 'true';
            state.pending = btn.dataset.sheetOption;
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') [regionSheet, districtSheet].forEach(closeSheet);
    });

    $('[data-open-sheet="region-sheet"]').addEventListener('click', () => {
        state.pending = state.region;
        $$('[data-sheet-option]', regionSheet).forEach((b) => (b.dataset.selected = String(b.dataset.sheetOption === state.region)));
        openSheet(regionSheet);
    });

    $('[data-open-sheet="district-sheet"]').addEventListener('click', () => {
        if (!state.region) { openSheet(regionSheet); return; }
        const items = districts[state.region] || [`${state.region} shahri`, `${state.region} tumani`];
        renderOptions(districtSheet, items);
        $('[data-sheet-title]', districtSheet).textContent = t(`${state.region} — tuman / shahar`);
        state.pending = state.district;
        $$('[data-sheet-option]', districtSheet).forEach((b) => (b.dataset.selected = String(b.dataset.sheetOption === state.district)));
        openSheet(districtSheet);
    });

    $('[data-sheet-confirm]', regionSheet).addEventListener('click', () => {
        if (!state.pending) return;
        if (state.pending !== state.region) {
            state.district = null;
            setField('district', null, 'Tuman yoki shaharni tanlang');
        }
        state.region = state.pending;
        setField('region', `${state.region}${/shahri|viloyati|iston$/.test(state.region) ? '' : ' viloyati'}`, 'Viloyatni tanlang');
        showDelivery(state.region);
        closeSheet(regionSheet);
        // Viloyatdan keyin darhol tuman tanlash oynasini ochamiz
        setTimeout(() => $('[data-open-sheet="district-sheet"]').click(), 320);
    });

    $('[data-sheet-confirm]', districtSheet).addEventListener('click', () => {
        if (!state.pending) return;
        state.district = state.pending;
        setField('district', state.district, 'Tuman yoki shaharni tanlang');
        closeSheet(districtSheet);
    });

    // Avval kiritilgan qiymatlarni tiklash (yuboruvchi maydonlari foydalanuvchi profilidan avto-to'ldirilган bo'ladi)
    const saved = store.get();
    $$('input:not([type=hidden]), textarea', form).forEach((input) => {
        if (input.name === 'sender_name' || input.name === 'sender_phone') {
            const val = input.defaultValue || saved[input.name] || '';
            if (val) {
                input.value = input.dataset.mask ? formatPhone(val, input.dataset.mask) : val;
            }
            return;
        }
        if (!saved[input.name]) return;
        input.value = input.dataset.mask ? formatPhone(saved[input.name], input.dataset.mask) : saved[input.name];
        input.dispatchEvent(new Event('input'));
    });
    if (saved.region) {
        state.region = saved.region;
        setField('region', saved.region_label || saved.region, 'Viloyatni tanlang');
        showDelivery(saved.region);
    }
    if (saved.district) {
        state.district = saved.district;
        setField('district', saved.district, 'Tuman yoki shaharni tanlang');
    }

    const validator = createValidator(form);
    const submitBtn = $('[data-submit]', form);
    const syncSubmit = () => { submitBtn.disabled = !validator.isComplete(); };
    form.addEventListener('input', syncSubmit);
    syncSubmit();

    // Viloyat/tuman tanlanganda xatoni olib tashlash
    [regionSheet, districtSheet].forEach((sheet) =>
        $('[data-sheet-confirm]', sheet).addEventListener('click', () => {
            setTimeout(() => {
                validator.check($('input[name="region"]', form));
                validator.check($('input[name="district"]', form));
                syncSubmit();
            });
        }));

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const firstInvalid = validator.checkAll();
        if (firstInvalid) {
            const target = firstInvalid.type === 'hidden' ? firstInvalid.closest('button') : firstInvalid;
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (target.tagName === 'INPUT') target.focus({ preventScroll: true });
            return;
        }

        const data = Object.fromEntries(new FormData(form));
        $$('input[data-prefix-value]', form).forEach((input) => {
            data[input.name] = input.value ? `${input.dataset.prefixValue} ${input.value}` : '';
        });
        data.region = state.region;
        data.region_label = $('[data-value-for="region"]').textContent;
        data.district = state.district;
        store.set(data);
        location.href = form.action;
    });
}

/* ---------- Forma validatsiyasi ---------- */
const UZ_OPERATORS = ['20', '33', '50', '55', '61', '62', '65', '66', '67', '69', '70', '71', '72', '73', '74', '75', '76', '77', '78', '79', '88', '90', '91', '93', '94', '95', '97', '98', '99'];

const RULES = {
    name(v) {
        if (v.length < 2) return t('Ism kamida 2 ta harfdan iborat bo‘lsin');
        if (!/^[\p{L}\s'‘’ʼ().-]+$/u.test(v)) return t('Ismda faqat harflar bo‘lishi mumkin');
        return null;
    },
    'phone-ru'(v) {
        const d = v.replace(/\D/g, '');
        if (d.length < 10) return t('Raqam to‘liq emas. Masalan: +7 900 123 - 45 - 67');
        return null;
    },
    'phone-uz'(v) {
        const d = v.replace(/\D/g, '');
        if (d.length < 9) return t('Raqam to‘liq emas. Masalan: +998 90 123 - 45 - 67');
        if (!UZ_OPERATORS.includes(d.slice(0, 2))) return t('Operator kodi noto‘g‘ri: ') + d.slice(0, 2);
        return null;
    },
    address(v) {
        if (v.length < 5) return t('Manzilni to‘liqroq yozing: mahalla, ko‘cha va uy raqami');
        return null;
    },
};

const EMPTY_MESSAGES = {
    name: 'Ismni kiriting',
    'phone-ru': 'Telefon raqamingizni kiriting',
    'phone-uz': 'Telefon raqamini kiriting',
    address: 'Manzilni kiriting',
};

function createValidator(form) {
    const inputs = $$('input[data-required], input[data-rule]', form);
    const touched = new Set();

    const validate = (input) => {
        const v = input.value.trim();
        const rule = input.dataset.rule;
        if (!v) {
            if (!('required' in input.dataset)) return null; // ixtiyoriy va bo'sh — xato emas
            return input.dataset.message || t(EMPTY_MESSAGES[rule] || 'Maydonni to‘ldiring');
        }
        let error = rule && RULES[rule] ? RULES[rule](v) : null;
        // qo'shimcha raqam asosiy raqam bilan bir xil bo'lmasin
        if (!error && input.name === 'recipient_phone_2') {
            const main = $('[name="recipient_phone"]', form).value.replace(/\D/g, '');
            if (main && main === v.replace(/\D/g, '')) error = t('Qo‘shimcha raqam asosiy raqamdan farq qilsin');
        }
        return error;
    };

    const show = (input, error) => {
        const field = input.closest('[data-field]');
        if (!field) return;
        field.dataset.invalid = String(!!error);
        $('[data-error]', field).textContent = error || '';
        if (input.type !== 'hidden') input.setAttribute('aria-invalid', String(!!error));
    };

    const check = (input) => {
        const error = validate(input);
        show(input, error);
        return error;
    };

    inputs.forEach((input) => {
        if (input.type === 'hidden') return;
        // maydondan chiqqanda tekshiramiz (agar foydalanuvchi biror narsa yozgan bo'lsa)
        input.addEventListener('blur', () => {
            if (input.value.trim() || touched.has(input)) { touched.add(input); check(input); }
        });
        // xato ko'rsatilgan bo'lsa — yozish paytida jonli yangilanadi
        input.addEventListener('input', () => {
            if (input.closest('[data-field]').dataset.invalid === 'true') check(input);
        });
    });

    return {
        check,
        // xato ko'rsatmasdan, faqat forma tayyorligini tekshiradi
        isComplete: () => inputs.every((input) => !validate(input)),
        checkAll() {
            let first = null;
            inputs.forEach((input) => {
                touched.add(input);
                if (check(input) && !first) first = input;
            });
            return first;
        },
    };
}

/* ---------- To'lov sahifasi: xulosa kartasi ---------- */
function initPaymentSummary() {
    const phone = $('[data-summary="phone"]');
    if (!phone) return;
    const s = store.get();
    if (s.recipient_phone) phone.textContent = s.recipient_phone.replace(/ - /g, '-');
    if (s.region && s.district) {
        $('[data-summary="address"]').textContent = t([s.region, s.district.replace(/ (shahri|tumani)$/, ''), s.address].filter(Boolean).join(', '));
        const regEl = $('[data-summary="region"]');
        if (regEl) regEl.textContent = t(s.region);
    }
    const wishInput = $('[data-wish-input]');
    if (wishInput) {
        if (s.wish && !wishInput.value) wishInput.value = s.wish;
        wishInput.addEventListener('input', () => store.set({ wish: wishInput.value }));
    }
    $$('input[data-checkout-field]').forEach((input) => {
        const key = input.dataset.checkoutField;
        if ((key === 'product_slug' || key === 'wish') && input.value) return;
        if (s[key]) input.value = s[key];
    });

    const paymentSheet = $('#payment-method-sheet');
    const paymentForm = $('#payment-form');
    const methodInput = $('#payment-method-input');
    if (paymentSheet && paymentForm) {
        $('[data-open-payment-sheet]')?.addEventListener('click', () => openSheet(paymentSheet));
        $$('[data-close-payment-sheet]', paymentSheet).forEach((el) =>
            el.addEventListener('click', () => closeSheet(paymentSheet)));
        $$('[data-select-payment-method]', paymentSheet).forEach((btn) =>
            btn.addEventListener('click', () => {
                if (methodInput) methodInput.value = btn.dataset.selectPaymentMethod;
                paymentForm.submit();
            }));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && paymentSheet.dataset.open === 'true') closeSheet(paymentSheet);
        });
    }
}

/* ---------- Avtomatik yo'naltirish (to'lov tekshirilmoqda) ---------- */
function initRedirect() {
    const el = $('[data-redirect]');
    if (!el) return;
    setTimeout(() => location.replace(el.dataset.redirect), Number(el.dataset.redirectAfter || 3000));
}

/* ---------- Faol buyurtma kartasi ---------- */
// Faol buyurtma localStorage'da saqlanadi (backend ulanganda API'dan olinadi):
// { id, status: 'shipping' | 'delivered', createdAt, recipient, region, district }
const activeOrder = {
    get() {
        try {
            const o = JSON.parse(localStorage.getItem(ORDER_KEY));
            return o && typeof o === 'object' && o.id ? o : null;
        } catch { return null; }
    },
    set(order) {
        try { localStorage.setItem(ORDER_KEY, JSON.stringify(order)); } catch {}
    },
};

function initActiveOrder() {
    const params = new URLSearchParams(location.search);
    const orderPage = location.pathname.match(/^\/orders\/([^/]+)/);
    const statusSection = $('[data-order-status]');
    const pageStatus = statusSection?.dataset.orderStatus;

    // 1) Buyurtma sahifasida: serverdagi haqiqiy status bilan localStorage'ni sinxronlaymiz
    if (orderPage && (params.get('success') === '1' || pageStatus)) {
        const c = store.get();
        const existing = activeOrder.get();
        const currentStatus = pageStatus || 'paid';
        activeOrder.set({
            ...(existing && existing.id === orderPage[1] ? existing : {}),
            id: orderPage[1],
            status: currentStatus,
            createdAt: (existing && existing.id === orderPage[1] && existing.createdAt) ? existing.createdAt : new Date().toISOString(),
            recipient: statusSection?.dataset.orderRecipient || c.recipient_name || existing?.recipient || 'Mohira Karimova',
            region: statusSection?.dataset.orderRegion || c.region || existing?.region || 'Farg‘ona',
            district: statusSection?.dataset.orderDistrict || c.district || existing?.district || 'Marg‘ilon shahri',
        });

        // Admin panelda status o'zgarsa, mijozdagi buyurtma sahifasi avtomatik yangilanadi
        const orderId = orderPage[1];
        const checkOrderPageStatus = async () => {
            if (document.hidden) return;
            try {
                const res = await fetch(`/orders/${encodeURIComponent(orderId)}/status`, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.found && data.status && data.status !== currentStatus) {
                    activeOrder.set({
                        id: data.id,
                        status: data.status,
                        createdAt: data.createdAt || new Date().toISOString(),
                        date: data.date,
                        recipient: data.recipient,
                        region: data.region,
                        district: data.district,
                        total: data.total,
                        items_count: data.items_count,
                        images: data.images,
                    });
                    location.replace(`/orders/${encodeURIComponent(orderId)}`);
                }
            } catch {}
        };
        setInterval(checkOrderPageStatus, 3000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) checkOrderPageStatus();
        });
    }

    // 2) Bosh sahifa kartasi
    const card = $('#active-order');
    if (!card) return;
    let statuses = {};
    let colors = {};
    let serverOrders = {};
    try {
        statuses = JSON.parse(card.dataset.statuses);
        colors = JSON.parse(card.dataset.statusColors);
        serverOrders = JSON.parse(card.dataset.serverOrders || '{}');
    } catch {}

    const latestOrderId = card.dataset.latestOrderId;
    let order = activeOrder.get();

    // Server ma'lumotlari bilan sinxronlash (status, qabul qiluvchi, summa, tovarlar)
    if (order && serverOrders[order.id]) {
        const srv = serverOrders[order.id];
        order = {
            ...order,
            status: srv.status || order.status,
            recipient: srv.recipient || order.recipient,
            region: srv.region || order.region,
            district: srv.district || order.district,
            createdAt: srv.createdAt || order.createdAt,
            date: srv.date,
            total: srv.total,
            items_count: srv.items_count,
            images: srv.images,
        };
        activeOrder.set(order);
    } else if (latestOrderId && serverOrders[latestOrderId]) {
        // Agar localStorage'dagi buyurtma o'chirilgan bo'lsa yoki bo'sh bo'lsa, oxirgi faol buyurtmani ko'rsatamiz
        const srv = serverOrders[latestOrderId];
        order = {
            id: srv.id,
            status: srv.status,
            createdAt: srv.createdAt || new Date().toISOString(),
            date: srv.date,
            recipient: srv.recipient,
            region: srv.region,
            district: srv.district,
            total: srv.total,
            items_count: srv.items_count,
            images: srv.images,
        };
        activeOrder.set(order);
    }

    const renderHomeCard = (ord) => {
        const status = ord && statuses[ord.status];
        // Buyurtma yo'q yoki topshirilgan bo'lsa — karta ko'rinmaydi
        if (!ord || !status || !status.active) {
            card.classList.add('hidden');
            return;
        }

        const numEl = $('[data-order-number]', card);
        if (numEl) numEl.textContent = `#${ord.id}`;

        const totalEl = $('[data-order-total]', card);
        if (totalEl && ord.total) totalEl.textContent = ord.total;

        const countEl = $('[data-order-count]', card);
        if (countEl && ord.items_count) countEl.textContent = `${ord.items_count} ${t('ta mahsulot')}`;

        const imagesBox = $('[data-order-images]', card);
        if (imagesBox && Array.isArray(ord.images) && ord.images.length > 0) {
            imagesBox.innerHTML = ord.images
                .map((src) => `<img src="${src}" alt="" class="h-[64px] w-[52px] shrink-0 rounded-[12px] bg-white object-cover">`)
                .join('');
        }

        const palette = colors[status.color] || {};
        const badge = $('[data-order-badge]', card);
        badge.textContent = t(status.badge);
        badge.className = 'rounded-[8px] border border-white/80 px-[8px] py-[4px] text-[11px] leading-normal font-semibold whitespace-nowrap';
        badge.style.background = palette.bg;
        badge.style.color = palette.text;

        $$('[data-order-bars] > span', card).forEach((bar, i) => {
            bar.style.background = i < status.step ? palette.accent : '';
        });

        $('[data-order-link]', card).href = `/orders/${ord.id}`;

        if (ord.date) {
            $('[data-order-date]', card).textContent = ord.date;
        } else {
            const d = new Date(ord.createdAt);
            const pad = (n) => String(n).padStart(2, '0');
            if (!Number.isNaN(d.getTime())) {
                $('[data-order-date]', card).textContent = `${pad(d.getDate())}.${pad(d.getMonth() + 1)}.${d.getFullYear()}`;
            }
        }
        const name = (ord.recipient || '').replace(/\s*\(.*\)\s*$/, '');
        const district = (ord.district || '').replace(/ (shahri|tumani)$/, '');
        $('[data-order-to]', card).textContent = t(`${name} · ${ord.region}${district ? ', ' + district : ''}`);
        card.classList.remove('hidden');
    };

    renderHomeCard(order);

    // Bosh sahifada ham admin panelda status o'zgarsa jonli yangilanadi
    if (order && order.id) {
        const pollHomeOrder = async () => {
            if (document.hidden) return;
            try {
                const res = await fetch(`/orders/${encodeURIComponent(order.id)}/status`, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });
                if (!res.ok) return;
                const data = await res.json();
                if (data.found) {
                    order = { ...order, ...data };
                    activeOrder.set(order);
                    renderHomeCard(order);
                }
            } catch {}
        };
        setInterval(pollHomeOrder, 3000);
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) pollHomeOrder();
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initGallery();
    initViewer();
    initShare();
    initBannerCarousel();
    initCollapsible();
    initLoadMore();
    initPhoneMasks();
    initAutogrow();
    initCheckout();
    initPaymentSummary();
    initRedirect();
    initActiveOrder();
});
