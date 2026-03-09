let cats = [];
let tags = [];


/* カテゴリ */

function addCat() {

    let v = document.getElementById("catInput").value.trim();

    if (!v) return;

    if (cats.includes(v)) {
        alert('このカテゴリは既に追加されています。');
        return;
    }

    cats.push(v);

    document.getElementById("catInput").value = "";

    renderCats();

}

function renderCats() {

    let area = document.getElementById("catCandidates");
    let sel = document.getElementById("catSelect");

    area.innerHTML = "";
    sel.innerHTML = "";

    if (cats.length === 0) {
        area.innerHTML = "<p style='color: #666;'>カテゴリが追加されていません</p>";
        return;
    }

    cats.forEach((c, i) => {

        area.innerHTML += `
<div style="display: inline-block; margin: 5px; padding: 5px 10px; background: #f0f0f0; border-radius: 3px;">
    ${c} <button type="button" onclick="delCat(${i})" style="margin-left: 5px; cursor: pointer;">×</button>
</div>
`;

        sel.innerHTML += `
<input type="hidden" name="cats[]" value="${c}">
`;

    });

}

function delCat(i) {

    cats.splice(i, 1);

    renderCats();

}



/* タグ */

function addTag() {

    let v = document.getElementById("tagInput").value.trim();

    if (!v) return;

    if (tags.includes(v)) {
        alert('このタグは既に追加されています。');
        return;
    }

    tags.push(v);

    document.getElementById("tagInput").value = "";

    renderTags();

}

function renderTags() {

    let area = document.getElementById("tagCandidates");
    let sel = document.getElementById("tagSelect");

    area.innerHTML = "";
    sel.innerHTML = "";

    if (tags.length === 0) {
        area.innerHTML = "<p style='color: #666;'>タグが追加されていません</p>";
        return;
    }

    tags.forEach((t, i) => {

        area.innerHTML += `
<div style="display: inline-block; margin: 5px; padding: 5px 10px; background: #f0f0f0; border-radius: 3px;">
    ${t} <button type="button" onclick="delTag(${i})" style="margin-left: 5px; cursor: pointer;">×</button>
</div>
`;

        sel.innerHTML += `
<input type="hidden" name="tags[]" value="${t}">
`;

    });

}

function delTag(i) {

    tags.splice(i, 1);

    renderTags();

}



/* 画像すべて選択 */

document.addEventListener("DOMContentLoaded", () => {

    let all = document.getElementById("allImages");

    if (all) {
        all.addEventListener("change", () => {

            document.querySelectorAll(".imgCheck").forEach(c => {
                c.checked = all.checked;
            });

        });
    }

    // Enterキーでカテゴリ・タグを追加
    let catInput = document.getElementById("catInput");
    if (catInput) {
        catInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                addCat();
            }
        });
    }

    let tagInput = document.getElementById("tagInput");
    if (tagInput) {
        tagInput.addEventListener("keypress", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                addTag();
            }
        });
    }

    // 初期表示
    renderCats();
    renderTags();

});
