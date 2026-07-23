describe("Review flow", () => {
    beforeEach(() => {
        cy.task("seedDb");
    });

    it("lists seeded reviews and submits a new one", () => {
        cy.visit("/");
        cy.contains("TestCorp");
        cy.contains("Example Ltd");

        cy.contains("a", "Új vélemény írása").click();
        cy.url().should("include", "/review/new");

        cy.get('input[name="review[companyName]"]').type("Új Cég Zrt.");
        cy.get('[data-star-value="5"]').click();
        cy.get('input[name="review[rating]"]').should("have.value", "5");
        cy.get('textarea[name="review[reviewText]"]').type("Nagyon profi csapat, gyors és megbízható.");
        cy.get('input[name="review[authorEmail]"]').type("teszt@ujceg.hu");
        cy.get('button[type="submit"]').click();

        cy.url().should("eq", Cypress.config("baseUrl") + "/");
        cy.get(".alert-success").should("contain", "Köszönjük a véleményed!");
        cy.contains("Új Cég Zrt.");
    });

    it("shows company statistics", () => {
        cy.visit("/companies");
        cy.contains("Cégek statisztikái");
        cy.contains("TestCorp");
        cy.contains("Example Ltd");
    });

    it("filters company statistics using autocomplete", () => {
        cy.visit("/companies");

        cy.get("#company-search").type("Test");
        cy.get(".company-autocomplete-results").should("be.visible");
        cy.get(".company-autocomplete-results").contains("TestCorp").click();

        cy.get('[data-company-stats-filter-target="row"]:visible').should("have.length", 1);
        cy.get('[data-company-stats-filter-target="row"]:visible').should("contain", "TestCorp");
        cy.get('[data-company-stats-filter-target="empty"]').should("not.be.visible");

        cy.get("#company-search").clear().type("NoSuchCompany");
        cy.get('[data-company-stats-filter-target="row"]:visible').should("have.length", 0);
        cy.get('[data-company-stats-filter-target="empty"]').should("be.visible");
    });

    it("paginates seeded reviews", () => {
        cy.visit("/");

        cy.get(".review-card").should("have.length", 10);
        cy.get(".cti-pagination__count").should("contain", "15");
        cy.contains("Kiváló szolgáltatás, nagyon elégedett vagyok.");

        cy.get('a[rel="next"]').click();

        cy.url().should("include", "page=2");
        cy.get(".review-card").should("have.length", 5);
        cy.get('.page-link[aria-current="page"]').should("have.text", "2");
        cy.contains("Jó termékek, de a szállítás csúszott.");
        cy.contains("Kiváló szolgáltatás, nagyon elégedett vagyok.").should("not.exist");
    });

    it("suggests existing companies while typing", () => {
        cy.visit("/review/new");
        cy.get('input[name="review[companyName]"]').type("Test");
        cy.get(".company-autocomplete-results").should("be.visible");
        cy.get(".company-autocomplete-results").contains("TestCorp").click();
        cy.get('input[name="review[companyName]"]').should("have.value", "TestCorp");
    });
});
