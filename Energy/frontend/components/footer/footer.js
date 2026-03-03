// Footer Component
var footerComponent = {
    init: function() {
        this.loadFooterHTML();
    },
    
    loadFooterHTML: function() {
        var container = document.getElementById('footer-component');
        if (!container) return;
        
        fetch('/Energy/frontend/components/footer/footer.html')
            .then(function(response) { return response.text(); })
            .then(function(html) {
                container.innerHTML = html;
                
                // Cargar CSS del footer
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = '/Energy/frontend/components/footer/footer.css';
                document.head.appendChild(link);
            })
            .catch(function(error) {});
    }
};

window.footerComponent = footerComponent;
