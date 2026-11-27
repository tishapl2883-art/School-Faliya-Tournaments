<?php $__env->startSection('title', ($entry ? 'Edit Team' : 'Create Team').' | Cric11'); ?>
<?php $__env->startSection('content'); ?>
<div class="actions">
    <a class="btn small" href="<?php echo e(route('cric11.match', $fixture)); ?>">&larr; Back to Match</a>
    <span class="badge"><?php echo e($fixture->teamA->short_name); ?> vs <?php echo e($fixture->teamB->short_name); ?></span>
</div>

<?php if($limits['team_size'] === 0): ?>
    <div class="card empty">
        Squads for <strong><?php echo e($fixture->teamA->name); ?></strong> and <strong><?php echo e($fixture->teamB->name); ?></strong> have no approved players yet.
        Add players to both teams in Admin first, then come back to build your Cric11 team.
    </div>
<?php else: ?>
<?php
    $playersJson = $pool->map(fn ($p) => [
        'id' => (int) $p->id,
        'name' => $p->name,
        'team_id' => (int) $p->team_id,
        'team_short' => $p->team?->short_name ?? '',
        'role' => $p->role,
        'role_code' => $p->roleCode(),
        'credits' => $p->fantasyCredits(),
        'photo' => $p->photoUrl(),
    ])->values();
?>

<div class="cric-bar" id="cric-bar">
    <div class="cric-bar-top">
        <div class="cric-kpi">
            <div><strong id="k-count">0 / <?php echo e($limits['team_size']); ?></strong><small>Players</small></div>
            <div><strong id="k-credits"><?php echo e(number_format($limits['max_credits'], 1)); ?></strong><small>Credits Left</small></div>
            <div><strong id="k-split">0 : 0</strong><small><?php echo e($fixture->teamA->short_name); ?> : <?php echo e($fixture->teamB->short_name); ?></small></div>
        </div>
        <button type="button" class="btn gold small" id="btn-step2" disabled>Next: Pick C &amp; VC</button>
    </div>
    <div class="cric-dots" id="k-dots" style="grid-template-columns:repeat(<?php echo e($limits['team_size']); ?>,1fr)">
        <?php for($i = 0; $i < $limits['team_size']; $i++): ?><span></span><?php endfor; ?>
    </div>
</div>

<form method="post" action="<?php echo e($entry ? route('cric11.update', $entry) : route('cric11.store', $fixture)); ?>" id="cric11-form">
    <?php echo csrf_field(); ?>
    <?php if($entry): ?> <?php echo method_field('put'); ?> <?php endif; ?>
    <div id="hidden-players"></div>

    <div id="step-1">
        <div class="cric-tabs" id="role-tabs">
            <?php $__currentLoopData = $limits['roles']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <button type="button" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['cric-tab', 'active' => $loop->first]); ?>" data-role="<?php echo e($code); ?>">
                    <?php echo e($code); ?> (<span data-rcount="<?php echo e($code); ?>">0</span>)
                    <small><?php echo e($rule['min'] === $rule['max'] ? $rule['min'] : $rule['min'].'-'.$rule['max']); ?></small>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="filters" style="margin-bottom:10px">
            <button type="button" class="btn small" data-team="all">All Teams</button>
            <button type="button" class="btn small" data-team="<?php echo e($fixture->team_a_id); ?>" style="background:#e8eef7;color:var(--navy)"><?php echo e($fixture->teamA->short_name); ?></button>
            <button type="button" class="btn small" data-team="<?php echo e($fixture->team_b_id); ?>" style="background:#e8eef7;color:var(--navy)"><?php echo e($fixture->teamB->short_name); ?></button>
            <span id="builder-hint" style="font-size:12px;color:var(--muted);margin-left:auto">Max <?php echo e($limits['max_per_team']); ?> from 1 team</span>
        </div>

        <div class="cric-split">
            <div class="cric-list" id="player-list"></div>

            <div>
                <div class="pitch" id="live-pitch">
                    <div class="pitch-label">Live Pitch Preview</div>
                    <div id="pitch-body" style="font-size:13px;color:rgba(255,255,255,.8);padding:24px 0">Tap + on players to build your XI</div>
                </div>
            </div>
        </div>
    </div>

    <div id="step-2" class="card" hidden>
        <div class="actions" style="justify-content:space-between;align-items:center">
            <h2 class="sub" style="margin:0">Choose Captain (2x) &amp; Vice-Captain (1.5x)</h2>
            <button type="button" class="btn small" id="btn-step1">&larr; Back to Players</button>
        </div>
        <p style="font-size:13px;color:var(--muted);margin-bottom:10px">Captain earns <strong>2x points</strong> and Vice-Captain earns <strong>1.5x points</strong>.</p>
        <div id="cvc-list" style="margin-bottom:16px"></div>

        <div class="form">
            <label>Your Name
                <input name="participant_name" value="<?php echo e(old('participant_name', $entry?->participant_name ?? session('cric11_name'))); ?>" required maxlength="80" placeholder="e.g. Rahul Patel">
            </label>
            <?php if(! $entry): ?>
                <label>Mobile Number (10 digits)
                    <input name="participant_mobile" value="<?php echo e(old('participant_mobile', session('cric11_mobile'))); ?>" required pattern="[6-9][0-9]{9}" maxlength="10" placeholder="9876543210">
                </label>
            <?php else: ?>
                <label>Mobile Number
                    <input value="<?php echo e($entry->participant_mobile); ?>" disabled>
                </label>
            <?php endif; ?>
            <label class="full">Fantasy Team Name
                <input name="team_name" value="<?php echo e(old('team_name', $defaultTeamName)); ?>" required maxlength="60" placeholder="e.g. Parnera Strikers XI">
            </label>
            <button class="btn gold full" type="submit" id="btn-submit"><?php echo e($entry ? 'Save Team Changes' : 'Save & Join Cric11'); ?></button>
        </div>
    </div>
</form>

<script>
(() => {
    const POOL = <?php echo json_encode($playersJson, 15, 512) ?>;
    const LIMITS = <?php echo json_encode($limits, 15, 512) ?>;
    const TEAM_A = <?php echo e((int) $fixture->team_a_id); ?>;
    const TEAM_B = <?php echo e((int) $fixture->team_b_id); ?>;
    const TEAM_NAMES = { [TEAM_A]: <?php echo json_encode($fixture->teamA->short_name, 15, 512) ?>, [TEAM_B]: <?php echo json_encode($fixture->teamB->short_name, 15, 512) ?> };

    const selected = new Set(<?php echo json_encode(array_map('intval', $selectedIds), 512) ?>);
    let captainId = <?php echo e((int) $captainId); ?> || null;
    let viceCaptainId = <?php echo e((int) $viceCaptainId); ?> || null;
    let activeRole = Object.keys(LIMITS.roles)[0] || 'WK';
    let activeTeam = 'all';

    const byId = Object.fromEntries(POOL.map(p => [p.id, p]));
    const listEl = document.getElementById('player-list');
    const pitchEl = document.getElementById('pitch-body');
    const hiddenEl = document.getElementById('hidden-players');
    const hintEl = document.getElementById('builder-hint');
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const btnStep2 = document.getElementById('btn-step2');

    function stats() {
        let credits = 0, a = 0, b = 0;
        const roles = { WK: 0, BAT: 0, AR: 0, BOWL: 0 };
        selected.forEach(id => {
            const p = byId[id];
            if (!p) return;
            credits += p.credits;
            if (p.team_id === TEAM_A) a++; else b++;
            roles[p.role_code]++;
        });
        return { credits: Math.round(credits * 10) / 10, a, b, roles };
    }

    function canAdd(p, st) {
        if (selected.has(p.id)) return { ok: true };
        if (selected.size >= LIMITS.team_size) return { ok: false, reason: 'Team full' };
        if (Math.round((LIMITS.max_credits - st.credits - p.credits) * 10) < 0) return { ok: false, reason: 'Low credits' };
        const tc = p.team_id === TEAM_A ? st.a : st.b;
        if (tc >= LIMITS.max_per_team) return { ok: false, reason: `Max ${LIMITS.max_per_team} per team` };
        if (st.roles[p.role_code] >= LIMITS.roles[p.role_code].max) return { ok: false, reason: `${p.role_code} max reached` };

        const slotsAfter = LIMITS.team_size - selected.size - 1;
        let unmet = 0;
        for (const [code, rule] of Object.entries(LIMITS.roles)) {
            const have = st.roles[code] + (code === p.role_code ? 1 : 0);
            if (have < rule.min) unmet += (rule.min - have);
        }
        if (unmet > slotsAfter) return { ok: false, reason: 'Save slots for required roles' };
        return { ok: true };
    }

    function isComplete(st) {
        if (selected.size !== LIMITS.team_size) return false;
        for (const [code, rule] of Object.entries(LIMITS.roles)) {
            if (st.roles[code] < rule.min || st.roles[code] > rule.max) return false;
        }
        return true;
    }

    function render() {
        const st = stats();
        const left = Math.round((LIMITS.max_credits - st.credits) * 10) / 10;
        document.getElementById('k-count').textContent = `${selected.size} / ${LIMITS.team_size}`;
        document.getElementById('k-credits').textContent = left.toFixed(1);
        document.getElementById('k-split').textContent = `${st.a} : ${st.b}`;
        document.querySelectorAll('#k-dots span').forEach((d, i) => d.classList.toggle('on', i < selected.size));

        for (const code of Object.keys(LIMITS.roles)) {
            const el = document.querySelector(`[data-rcount="${code}"]`);
            if (el) el.textContent = st.roles[code];
        }

        const complete = isComplete(st);
        btnStep2.disabled = !complete;
        hintEl.textContent = complete ? 'Team ready! Click "Next: Pick C & VC"' : `Pick ${LIMITS.team_size - selected.size} more (max ${LIMITS.max_per_team} per team)`;

        const filtered = POOL.filter(p => p.role_code === activeRole && (activeTeam === 'all' || p.team_id === Number(activeTeam)));
        listEl.innerHTML = filtered.length ? filtered.map(p => {
            const picked = selected.has(p.id);
            const check = canAdd(p, st);
            return `<div class="cric-p ${picked ? 'picked' : (!check.ok ? 'disabled' : '')}">
                <img src="${p.photo}" alt="">
                <div class="cric-p-info">
                    <strong>${escapeHtml(p.name)}</strong>
                    <small>${escapeHtml(p.team_short)} &middot; ${escapeHtml(p.role)}${!picked && !check.ok ? ' &middot; ' + check.reason : ''}</small>
                </div>
                <span class="cric-cr">${p.credits.toFixed(1)}</span>
                <button type="button" class="cric-add" data-toggle="${p.id}" ${!picked && !check.ok ? 'disabled' : ''}>${picked ? '✓' : '+'}</button>
            </div>`;
        }).join('') : '<div class="card empty">No players in this filter.</div>';

        // Pitch preview
        if (!selected.size) {
            pitchEl.innerHTML = '<div style="font-size:13px;color:rgba(255,255,255,.8);padding:24px 0">Tap + on players to build your XI</div>';
        } else {
            const groups = { WK: [], BAT: [], AR: [], BOWL: [] };
            selected.forEach(id => { if (byId[id]) groups[byId[id].role_code].push(byId[id]); });
            pitchEl.innerHTML = Object.entries(groups).filter(([, arr]) => arr.length).map(([code, arr]) => `
                <div class="pitch-row">
                    <div class="pitch-label">${LIMITS.roles[code].label}</div>
                    <div class="pitch-players">
                        ${arr.map(p => `<div class="pitch-node">
                            <img src="${p.photo}" alt="">
                            <span class="pitch-tag ${p.team_id === TEAM_B ? 'away' : ''}">${escapeHtml(p.name)}</span>
                        </div>`).join('')}
                    </div>
                </div>
            `).join('');
        }

        // Hidden inputs
        hiddenEl.innerHTML = Array.from(selected).map(id => `<input type="hidden" name="players[]" value="${id}">`).join('');
    }

    function renderCvc() {
        const chosen = Array.from(selected).map(id => byId[id]).filter(Boolean);
        if (!selected.has(captainId)) captainId = chosen[0]?.id || null;
        if (!selected.has(viceCaptainId) || viceCaptainId === captainId) {
            viceCaptainId = chosen.find(p => p.id !== captainId)?.id || null;
        }
        document.getElementById('cvc-list').innerHTML = chosen.map(p => `
            <div class="cvc-row">
                <div>
                    <strong>${escapeHtml(p.name)}</strong>
                    <small style="display:block;color:var(--muted)">${escapeHtml(p.team_short)} &middot; ${p.roleCode || p.role_code} &middot; ${p.credits.toFixed(1)} Cr</small>
                </div>
                <div class="cvc-btns">
                    <label class="cvc-btn"><input type="radio" name="captain_id" value="${p.id}" ${captainId === p.id ? 'checked' : ''} required><span>C (2x)</span></label>
                    <label class="cvc-btn"><input type="radio" name="vice_captain_id" value="${p.id}" ${viceCaptainId === p.id ? 'checked' : ''} required><span>VC (1.5x)</span></label>
                </div>
            </div>
        `).join('');
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    }

    document.getElementById('role-tabs').addEventListener('click', e => {
        const btn = e.target.closest('[data-role]');
        if (!btn) return;
        activeRole = btn.dataset.role;
        document.querySelectorAll('#role-tabs .cric-tab').forEach(b => b.classList.toggle('active', b === btn));
        render();
    });

    document.querySelectorAll('[data-team]').forEach(btn => {
        btn.addEventListener('click', () => {
            activeTeam = btn.dataset.team;
            document.querySelectorAll('[data-team]').forEach(b => {
                b.style.background = b === btn ? 'var(--navy2)' : '#e8eef7';
                b.style.color = b === btn ? '#fff' : 'var(--navy)';
            });
            render();
        });
    });

    listEl.addEventListener('click', e => {
        const btn = e.target.closest('[data-toggle]');
        if (!btn) return;
        const id = Number(btn.dataset.toggle);
        if (selected.has(id)) selected.delete(id);
        else if (canAdd(byId[id], stats()).ok) selected.add(id);
        render();
    });

    btnStep2.addEventListener('click', () => {
        if (!isComplete(stats())) return;
        renderCvc();
        step1.hidden = true;
        step2.hidden = false;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.getElementById('btn-step1').addEventListener('click', () => {
        step2.hidden = true;
        step1.hidden = false;
    });

    document.getElementById('cvc-list').addEventListener('change', e => {
        if (e.target.name === 'captain_id') {
            captainId = Number(e.target.value);
            if (viceCaptainId === captainId) {
                const other = Array.from(selected).find(id => id !== captainId);
                if (other) {
                    viceCaptainId = other;
                    renderCvc();
                }
            }
        } else if (e.target.name === 'vice_captain_id') {
            viceCaptainId = Number(e.target.value);
            if (captainId === viceCaptainId) {
                const other = Array.from(selected).find(id => id !== viceCaptainId);
                if (other) {
                    captainId = other;
                    renderCvc();
                }
            }
        }
    });

    document.getElementById('cric11-form').addEventListener('submit', e => {
        if (!captainId || !viceCaptainId || captainId === viceCaptainId) {
            e.preventDefault();
            alert('Please choose a different Captain and Vice-Captain.');
        }
    });

    render();
})();
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/cric11/builder.blade.php ENDPATH**/ ?>