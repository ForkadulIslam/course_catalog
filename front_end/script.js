document.addEventListener("DOMContentLoaded", function () {
    const apiBaseUrl = 'http://localhost:8000/index.php';
    const categoriesList = document.getElementById("categoryTree");
    const coursesContainer = document.getElementById("courses");
    const pageHeader = document.getElementById("pageHeader");

    // Fetch categories and build tree
    function loadCategories() {
        fetch(apiBaseUrl + "/categories")
            .then(response => response.json())
            .then(data => {
                categoriesList.innerHTML = "";
                const categoryTree = buildCategoryTree(data);
                console.log(categoryTree)
                categoriesList.appendChild(categoryTree);
            })
            .catch(error => console.error("Error loading categories:", error));
    }

    // Recursive function to build category tree
    function buildCategoryTree(categories, parentId = null) {
        const ul = document.createElement("ul");

        categories
            .filter(category => category.parent === parentId)
            .forEach(category => {
                const li = document.createElement("li");
                const span = document.createElement("span");

                span.className = "category-item";
                span.textContent = `${category.name} (${category.count_of_courses})`;
                span.onclick = (event) => {
                    event.stopPropagation();
                    loadCourses(category.id);
                    pageHeader.innerHTML = category.name
                    span.classList.toggle("open");  // Toggle icon
                    if (li.lastElementChild.tagName === "UL") {
                        li.lastElementChild.classList.toggle("hidden");
                    }
                };

                const children = buildCategoryTree(categories, category.id);
                if (children.childNodes.length > 0) {
                    li.appendChild(span);
                    li.appendChild(children);
                } else {
                    li.appendChild(span);
                }

                ul.appendChild(li);
            });

        return ul;
    }




    // Fetch and display courses
    function loadCourses(categoryId = null) {
        let url = apiBaseUrl + "/courses";
        if (categoryId) url += `?category=${categoryId}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                coursesContainer.innerHTML = "";
                data.forEach(course => {
                    let div = document.createElement("div");
                    div.className = "course-card";
                    div.innerHTML = `
                        <img src="${course.image_preview}" alt="${course.title}" class="course-image">
                        <h3>${course.title}</h3>
                       <p class="course-category"><strong>Category:</strong> ${course.category_name}</p>
                        <p class="course-description">${truncateText(course.description, 100)}</p>
                    `;
                    coursesContainer.appendChild(div);
                });
            })
            .catch(error => console.error("Error loading courses:", error));
    }

    // Helper function to truncate text with ellipsis
    function truncateText(text, maxLength) {
        return text.length > maxLength ? text.substring(0, maxLength) + "..." : text;
    }

    // Initialize
    loadCategories();
    loadCourses();
});
