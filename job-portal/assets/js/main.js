document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        searchJobs();
    });

    async function searchJobs() {
        const formData = new FormData(searchForm);
        const includeExternal = document.getElementById('includeExternal').checked;
        
        try {
            // Show loading indicator
            document.getElementById('loadingIndicator').style.display = 'block';
            
            // 1. Search Local Jobs
            let localResponse = await fetch(`api/search_local.php?${new URLSearchParams(formData)}`);
            let localJobs = await localResponse.json();
            
            // 2. Search External Jobs if enabled
            let externalJobs = [];
            if (includeExternal) {
                let externalResponse = await fetch(`api/search_external.php?${new URLSearchParams(formData)}`);
                externalJobs = await externalResponse.json();
            }
            
            // Display results
            displayResults(localJobs.jobs, externalJobs.jobs || []);
            
        } catch (error) {
            console.error('Search error:', error);
            alert('Error searching jobs. Check console for details.');
        } finally {
            document.getElementById('loadingIndicator').style.display = 'none';
        }
    }

    function displayResults(localJobs, externalJobs) {
        const container = document.getElementById('jobsContainer');
        container.innerHTML = '';
        
        // Display local jobs
        localJobs.forEach(job => {
            container.appendChild(createJobCard(job));
        });
        
        // Display external jobs if any
        if (externalJobs.length > 0) {
            const externalHeader = document.createElement('h4');
            externalHeader.className = 'mt-5 mb-3';
            externalHeader.textContent = 'External Job Listings';
            container.appendChild(externalHeader);
            
            externalJobs.forEach(job => {
                container.appendChild(createJobCard(job));
            });
        }
    }
    
    function createJobCard(job) {
        // Your job card creation logic here
    }
});