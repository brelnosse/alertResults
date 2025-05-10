document.addEventListener("DOMContentLoaded", () => {
    // Animation pour le bouton
    const submitBtn = document.querySelector('button[type="submit"]')
    if (submitBtn) {
      submitBtn.addEventListener("mousedown", function () {
        this.style.transform = "scale(0.98)"
      })
  
      submitBtn.addEventListener("mouseup", function () {
        this.style.transform = "scale(1)"
      })
  
      submitBtn.addEventListener("mouseleave", function () {
        this.style.transform = "scale(1)"
      })
    }
  
    // Animation pour les champs de formulaire
    const inputs = document.querySelectorAll("input, select")
  
    inputs.forEach((input) => {
      input.addEventListener("focus", function () {
        this.style.boxShadow = "0 0 5px rgba(0, 64, 128, 0.5)"
      })
  
      input.addEventListener("blur", function () {
        this.style.boxShadow = "none"
        validateField(this)
      })
    })
  
    // Validation du numéro de téléphone camerounais
    function validateCameroonPhone(phone) {
      // Format: 6XXXXXXXX (9 chiffres commençant par 6)
      const regex = /^6[0-9]{8}$/
      return regex.test(phone)
    }
  
    // Validation de l'email
    function validateEmail(email) {
      const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      return regex.test(email)
    }
  
    // Validation du mot de passe (au moins 8 caractères, une majuscule, une minuscule et un chiffre)
    function validatePassword(password) {
      const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/
      return regex.test(password)
    }
  
    // Validation d'un champ
    function validateField(field) {
      const fieldName = field.name
      const fieldValue = field.value.trim()
      let errorMessage = ""
  
      // Réinitialiser l'état du champ
      field.classList.remove("error")
      const errorElement = field.nextElementSibling
      if (errorElement && errorElement.classList.contains("error-message")) {
        errorElement.style.display = "none"
      }
  
      // Validation selon le type de champ
      if (fieldValue === "" && field.hasAttribute("required")) {
        errorMessage = "Ce champ est obligatoire"
      } else if (fieldValue !== "") {
        switch (fieldName) {
          case "email":
            if (!validateEmail(fieldValue)) {
              errorMessage = "Adresse email invalide"
            }
            break
          case "phone":
            if (!validateCameroonPhone(fieldValue)) {
              errorMessage = "Format: 6XXXXXXXX (9 chiffres)"
            }
            break
          case "password":
            if (!validatePassword(fieldValue)) {
              errorMessage = "Min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre"
            }
            break
          case "confirmPassword":
            const password = document.querySelector('input[name="password"]').value
            if (fieldValue !== password) {
              errorMessage = "Les mots de passe ne correspondent pas"
            }
            break
          case "birthdate":
            if (field.type === "date") {
              const today = new Date()
              const birthDate = new Date(fieldValue)
              let age = today.getFullYear() - birthDate.getFullYear()
              const monthDiff = today.getMonth() - birthDate.getMonth()
  
              if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--
              }
  
              if (age < 15) {
                errorMessage = "Vous devez avoir au moins 15 ans"
              }
            }
            break
        }
      }
  
      // Afficher l'erreur si nécessaire
      if (errorMessage) {
        field.classList.add("error")
        if (errorElement && errorElement.classList.contains("error-message")) {
          errorElement.textContent = errorMessage
          errorElement.style.display = "block"
        }
        return false
      }
  
      return true
    }
  
    // Validation du formulaire complet
    const form = document.querySelector("form")
    if (form) {
      form.addEventListener("submit", (e) => {
        e.preventDefault()
  
        let isValid = true
        const fields = form.querySelectorAll("input, select")
  
        fields.forEach((field) => {
          // Ne valider les champs conditionnels que s'ils sont visibles/requis
          if (field.style.display !== "none" && !field.disabled) {
            if (!validateField(field)) {
              isValid = false
            }
          }
        })
  
        if (isValid) {
          submitBtn.innerHTML = '<span style="display: inline-block; animation: spin 1s infinite linear;">↻</span>'
  
          // Simuler l'envoi du formulaire
          setTimeout(() => {
            submitBtn.innerHTML = "S'inscrire"
            // Redirection vers la page de connexion
            form.submit()
          }, 1500)
        }
      })
    }
  
    // Animation de rotation pour l'icône de chargement
    document.head.insertAdjacentHTML(
      "beforeend",
      `
          <style>
              @keyframes spin {
                  from { transform: rotate(0deg); }
                  to { transform: rotate(360deg); }
              }
          </style>
      `,
    )
  
    // Gestion des champs conditionnels
    setupConditionalFields()
  })
  
  // Fonction pour gérer les champs conditionnels selon les sélections
  function setupConditionalFields() {
    // Pour la page d'inscription des administrateurs
    const roleSelect = document.querySelector('select[name="role"]')
    if (roleSelect) {
      const departmentGroup = document.querySelector("#department-group")
  
      roleSelect.addEventListener("change", function () {
        if (this.value === "directeur") {
          departmentGroup.querySelector("select").removeAttribute("required")
          departmentGroup.style.opacity = "0.5"
        } else {
          departmentGroup.querySelector("select").setAttribute("required", "required")
          departmentGroup.style.opacity = "1"
        }
      })
    }
  
    // Pour la page d'inscription des étudiants
    const cycleSelect = document.querySelector('select[name="cycle"]')
    const niveauSelect = document.querySelector('select[name="niveau"]')
    const specialiteSelect = document.querySelector('select[name="specialite"]')
  
    if (cycleSelect && niveauSelect && specialiteSelect) {
      cycleSelect.addEventListener("change", updateNiveauOptions)
      niveauSelect.addEventListener("change", updateSpecialiteOptions)
  
      // Initialiser les options
      updateNiveauOptions()
    }
  }
  
  // Mettre à jour les options de niveau en fonction du cycle
  function updateNiveauOptions() {
    const cycleSelect = document.querySelector('select[name="cycle"]')
    const niveauSelect = document.querySelector('select[name="niveau"]')
  
    if (!cycleSelect || !niveauSelect) return
  
    const cycle = cycleSelect.value
    niveauSelect.innerHTML = ""
  
    let options = []
  
    switch (cycle) {
      case "prepa-ingenieur":
      case "bts":
      case "dut":
        options = [
          { value: "1", text: "1ère année" },
          { value: "2", text: "2ème année" },
        ]
        break
      case "ingenieur":
        options = [
          { value: "3", text: "3ème année" },
          { value: "4", text: "4ème année" },
          { value: "5", text: "5ème année" },
        ]
        break
      case "licence":
        options = [{ value: "3", text: "3ème année" }]
        break
    }
  
    options.forEach((option) => {
      const optionElement = document.createElement("option")
      optionElement.value = option.value
      optionElement.textContent = option.text
      niveauSelect.appendChild(optionElement)
    })
  
    updateSpecialiteOptions()
  }
  
  // Mettre à jour les options de spécialité en fonction du cycle et du niveau
  function updateSpecialiteOptions() {
    const cycleSelect = document.querySelector('select[name="cycle"]')
    const niveauSelect = document.querySelector('select[name="niveau"]')
    const specialiteSelect = document.querySelector('select[name="specialite"]')
  
    if (!cycleSelect || !niveauSelect || !specialiteSelect) return
  
    const cycle = cycleSelect.value
    const niveau = niveauSelect.value
    specialiteSelect.innerHTML = ""
  
    let options = []
  
    if (cycle === "prepa-ingenieur" && (niveau === "1" || niveau === "2")) {
      options = [{ value: "prepa 3il", text: "Prépa 3iL" }]
    } else if (cycle === "dut" && (niveau === "1" || niveau === "2")) {
      options = [
        { value: "pam", text: "PAM" },
        { value: "rs", text: "RS" },
      ]
    } else if (cycle === "ingenieur" && niveau === "3") {
      options = [{ value: "ingenieur 1", text: "Ingénieur 1" }]
    } else if ((cycle === "ingenieur" && (niveau === "4" || niveau === "5")) || cycle === "licence") {
      options = [
        { value: "dev fullstack web", text: "Développement Fullstack Web" },
        { value: "data-science", text: "Data Science" },
        { value: "robotic", text: "Robotique" },
      ]
    } else if (cycle === "bts" && (niveau === "1" || niveau === "2")) {
      options = [
        { value: "iwd", text: "IWD" },
        { value: "gl", text: "GL" },
        { value: "rs", text: "RS" },
        { value: "msi", text: "MSI" },
        { value: "iia", text: "IIA" },
      ]
    }
  
    options.forEach((option) => {
      const optionElement = document.createElement("option")
      optionElement.value = option.value
      optionElement.textContent = option.text
      specialiteSelect.appendChild(optionElement)
    })
  }
  