@extends('layouts.app')

@section('title', 'Technician Skills Management')
@section('page-title', 'Technician Skills Management')

@section('content')
<!-- Toast Notification Container - Higher z-index than modal -->
<div id="toast-container" class="fixed top-4 right-4 z-[9999] space-y-2"></div>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Technician Skills</h3>
            <p class="text-sm text-slate-500 mt-1">Manage technician skills, certifications, and expertise levels</p>
        </div>
        <a href="{{ route('admin.technicians.index') }}" 
           class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors font-medium">
            Back to Technicians
        </a>
    </div>

    <!-- Technicians List -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Technician</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Skills</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Primary Skills</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($technicians as $technician)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $technician->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $technician->user->email }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @forelse($technician->skills as $skill)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $skill->complexity_level === 'expert' ? 'bg-purple-100 text-purple-800' : 
                                               ($skill->complexity_level === 'advanced' ? 'bg-blue-100 text-blue-800' : 
                                               ($skill->complexity_level === 'intermediate' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-800')) }}">
                                            {{ $skill->deviceType->name ?? 'N/A' }} 
                                            <span class="ml-1">({{ ucfirst($skill->complexity_level) }})</span>
                                        </span>
                                    @empty
                                        <span class="text-sm text-slate-400">No skills assigned</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @forelse($technician->primarySkills as $skill)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            {{ $skill->deviceType->name ?? 'N/A' }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-slate-400">None</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="openSkillModal({{ $technician->id }}, '{{ $technician->user->name }}')"
                                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    Manage Skills
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                No technicians found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Skill Management Modal -->
<div id="skill-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800">
                Manage Skills: <span id="modal-technician-name"></span>
            </h3>
            <button onclick="closeSkillModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div id="skill-modal-content">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Modern Toast Notification System - Define first so it's available everywhere
    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) {
            console.warn('Toast container not found, falling back to alert');
            alert(message);
            return;
        }
        
        const toast = document.createElement('div');
        const id = 'toast-' + Date.now();
        toast.id = id;
        
        const colors = {
            success: {
                bg: 'bg-green-500',
                border: 'border-green-600',
                icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
            },
            error: {
                bg: 'bg-red-500',
                border: 'border-red-600',
                icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
            },
            warning: {
                bg: 'bg-yellow-500',
                border: 'border-yellow-600',
                icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>'
            },
            info: {
                bg: 'bg-blue-500',
                border: 'border-blue-600',
                icon: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            }
        };
        
        const style = colors[type] || colors.info;
        
        toast.className = `${style.bg} ${style.border} border-l-4 text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 min-w-[300px] max-w-md transform transition-all duration-300 ease-in-out translate-x-full opacity-0`;
        toast.innerHTML = `
            <div class="flex-shrink-0">
                ${style.icon}
            </div>
            <div class="flex-1">
                <p class="font-medium">${message}</p>
            </div>
            <button onclick="closeToast('${id}')" class="flex-shrink-0 ml-2 text-white hover:text-gray-200 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
        }, 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            closeToast(id);
        }, 5000);
    }
    
    function closeToast(id) {
        const toast = document.getElementById(id);
        if (toast) {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
    }
    
    function openSkillModal(technicianId, technicianName) {
        document.getElementById('modal-technician-name').textContent = technicianName;
        const modal = document.getElementById('skill-modal');
        const content = document.getElementById('skill-modal-content');
        
        content.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div><p class="mt-2 text-slate-500">Loading...</p></div>';
        modal.classList.remove('hidden');
        
        // Load skills via web route (uses session auth)
        fetch(`/admin/technicians/${technicianId}/skills`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            renderSkillsContent(technicianId, data);
        })
        .catch(error => {
            console.error('Error loading skills:', error);
            content.innerHTML = '<div class="text-center py-8 text-red-600">Error loading skills. Please try again.</div>';
        });
    }
    
    function closeSkillModal() {
        document.getElementById('skill-modal').classList.add('hidden');
    }
    
    function renderSkillsContent(technicianId, data) {
        const content = document.getElementById('skill-modal-content');
        const skills = data.skills || [];
        
        let html = `
            <div class="mb-6">
                <button onclick="showAddSkillForm(${technicianId})" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    + Add Skill
                </button>
            </div>
            
            <div id="add-skill-form" class="hidden mb-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
                <h4 class="font-semibold text-slate-800 mb-4">Add New Skill</h4>
                <form id="add-skill-form-element" onsubmit="addSkill(event, ${technicianId})">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Device Type <span class="text-red-500">*</span></label>
                            <select name="device_type_id" id="device-type-select" required 
                                    class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select device type...</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Complexity Level <span class="text-red-500">*</span></label>
                            <select name="complexity_level" id="complexity-level-select" required 
                                    class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select level...</option>
                                <option value="basic" selected>Basic</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Experience (Years)</label>
                        <input type="number" 
                               name="experience_years" 
                               id="experience-years-input"
                               min="0" 
                               max="50" 
                               value="0"
                               class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500"
                               placeholder="Enter years of experience">
                        <p class="text-xs text-slate-500 mt-1">Number of years of experience with this device type</p>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="hideAddSkillForm()" 
                                class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition-colors font-medium">
                            Cancel
                        </button>
                        <button type="submit" 
                                id="add-skill-submit-btn"
                                disabled
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Add Skill
                            </span>
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="space-y-3">
        `;
        
        if (skills.length > 0) {
            skills.forEach(skill => {
                html += `
                    <div class="border border-slate-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-slate-800">${skill.device_type ? skill.device_type.name : 'N/A'}</p>
                                <div class="flex items-center gap-4 mt-2 text-sm text-slate-600">
                                    <span>Level: <span class="font-medium">${skill.complexity_level}</span></span>
                                    <span>Experience: <span class="font-medium">${skill.experience_years} years</span></span>
                                    <span>Jobs: <span class="font-medium">${skill.jobs_completed}</span></span>
                                    ${skill.success_rate ? `<span>Success: <span class="font-medium">${skill.success_rate}%</span></span>` : ''}
                                </div>
                            </div>
                            <div class="flex gap-2">
                                ${skill.is_primary ? '<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">Primary</span>' : ''}
                                <button onclick="deleteSkill(${technicianId}, ${skill.id})" 
                                        class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-sm">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            html += '<p class="text-center text-slate-500 py-8">No skills assigned. Click "Add Skill" to get started.</p>';
        }
        
        html += '</div>';
        content.innerHTML = html;
        
        // Load device types and update button state after loading
        loadDeviceTypes(technicianId).then(() => {
            // Update button state after device types are loaded
            setTimeout(() => {
                updateAddButtonState();
            }, 100);
        });
    }
    
    function loadDeviceTypes(technicianId) {
        return fetch('/admin/technician-skills/device-types', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('device-type-select');
            if (select && data.device_types) {
                // Clear existing options except the first one
                while (select.options.length > 1) {
                    select.remove(1);
                }
                data.device_types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    select.appendChild(option);
                });
            }
            return data;
        })
        .catch(error => {
            console.error('Error loading device types:', error);
            showToast('Error loading device types', 'error');
            return { device_types: [] };
        });
    }
    
    function showAddSkillForm(technicianId) {
        const form = document.getElementById('add-skill-form');
        form.classList.remove('hidden');
        
        // Reset form fields
        const deviceSelect = document.getElementById('device-type-select');
        const complexitySelect = document.getElementById('complexity-level-select');
        
        if (deviceSelect) {
            deviceSelect.value = '';
        }
        
        // Set default complexity level to "basic"
        if (complexitySelect) {
            complexitySelect.value = 'basic';
        }
        
        // Load device types if not already loaded
        loadDeviceTypes(technicianId).then(() => {
            // Update button state after device types are loaded
            // Complexity is already "basic", so button will enable when device type is selected
            setTimeout(() => {
                updateAddButtonState();
                
                // Attach event listeners after DOM is ready
                const deviceSelectEl = document.getElementById('device-type-select');
                const complexitySelectEl = document.getElementById('complexity-level-select');
                
                if (deviceSelectEl) {
                    deviceSelectEl.addEventListener('change', updateAddButtonState);
                }
                if (complexitySelectEl) {
                    complexitySelectEl.addEventListener('change', updateAddButtonState);
                }
            }, 100);
        }).catch(error => {
            console.error('Error loading device types:', error);
            showToast('Error loading device types', 'error');
        });
    }
    
    function hideAddSkillForm() {
        const formElement = document.getElementById('add-skill-form');
        if (formElement) {
            formElement.classList.add('hidden');
        }
        // Reset form
        const form = document.getElementById('add-skill-form-element');
        if (form) {
            form.reset();
            // Reset complexity to default
            const complexitySelect = document.getElementById('complexity-level-select');
            if (complexitySelect) {
                complexitySelect.value = 'basic';
            }
            // Reset experience to 0
            const experienceInput = document.getElementById('experience-years-input');
            if (experienceInput) {
                experienceInput.value = '0';
            }
            // Update button state
            updateAddButtonState();
        }
    }
    
    function updateAddButtonState() {
        const submitBtn = document.getElementById('add-skill-submit-btn');
        const deviceSelect = document.getElementById('device-type-select');
        const complexitySelect = document.getElementById('complexity-level-select');
        
        if (submitBtn && deviceSelect && complexitySelect) {
            const deviceSelected = deviceSelect.value && deviceSelect.value !== '';
            const complexitySelected = complexitySelect.value && complexitySelect.value !== '';
            
            // Enable button only if both fields are selected
            const shouldEnable = deviceSelected && complexitySelected;
            submitBtn.disabled = !shouldEnable;
            
            // Always keep button green - only adjust opacity and cursor
            // Remove any classes that might change the color
            submitBtn.classList.remove('bg-slate-400', 'bg-gray-500', 'bg-red-500', 'bg-blue-500', 'bg-yellow-500');
            submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');
            
            // Update visual state - only opacity and cursor, not color
            if (shouldEnable) {
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
    }
    
    function addSkill(event, technicianId) {
        event.preventDefault();
        event.stopPropagation();
        
        const form = event.target;
        const submitBtn = form.querySelector('#add-skill-submit-btn') || form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : 'Add Skill';
        
        // Disable button during submission
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="flex items-center gap-2"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Adding...</span>';
        }
        
        const formData = new FormData(form);
        
        // Validate form
        const deviceTypeId = formData.get('device_type_id');
        const complexityLevel = formData.get('complexity_level');
        const experienceYears = formData.get('experience_years');
        
        if (!deviceTypeId || !complexityLevel) {
            showToast('Please fill in all required fields', 'warning');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
            return;
        }
        
        // Convert experience_years to integer if provided
        if (experienceYears !== null && experienceYears !== '') {
            const experienceInt = parseInt(experienceYears, 10);
            if (!isNaN(experienceInt)) {
                formData.set('experience_years', experienceInt);
            } else {
                formData.set('experience_years', 0);
            }
        } else {
            formData.set('experience_years', 0);
        }
        
        fetch(`/admin/technicians/${technicianId}/skills`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    // Handle 422 validation errors (e.g., skill already exists)
                    const errorMessage = err.message || err.error || 'Failed to add skill';
                    throw new Error(errorMessage);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.message) {
                showToast(data.message, 'success');
                // Get technician name before reloading
                const technicianNameEl = document.getElementById('modal-technician-name');
                const technicianName = technicianNameEl ? technicianNameEl.textContent : '';
                
                // Hide the form
                hideAddSkillForm();
                
                // Reload the skills list without closing modal
                fetch(`/admin/technician-skills/${technicianId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(data => {
                    renderSkillsContent(technicianId, data);
                })
                .catch(error => {
                    console.error('Error reloading skills:', error);
                    // Fallback: reload modal
                    if (technicianName) {
                        openSkillModal(technicianId, technicianName);
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error adding skill:', error);
            showToast(error.message || 'Error adding skill. Please try again.', 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
    
    function deleteSkill(technicianId, skillId) {
        if (!confirm('Are you sure you want to remove this skill?')) return;
        
        fetch(`/admin/technicians/${technicianId}/skills/${skillId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.message) {
                alert(data.message);
                openSkillModal(technicianId, document.getElementById('modal-technician-name').textContent);
            }
        })
        .catch(error => {
            console.error('Error deleting skill:', error);
            showToast('Error deleting skill. Please try again.', 'error');
        });
    }
</script>
@endpush
@endsection
