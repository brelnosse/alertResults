document.addEventListener("DOMContentLoaded", () => {
    // Couleurs disponibles pour les cercles
    const colors = [
      "#80c0ff", // bleu clair
      "#ffcc80", // orange clair
      "#ffff80", // jaune
      "#003366", // bleu foncé
      "#ff9966", // orange
    ]
  
    // Fonction pour générer un nombre aléatoire entre min et max
    function randomBetween(min, max) {
      return Math.floor(Math.random() * (max - min + 1) + min)
    }
  
    // Fonction pour générer une position aléatoire pour un cercle
    function generateRandomCircle(container) {
      const containerWidth = container.offsetWidth
      const containerHeight = container.offsetHeight
  
      // Taille aléatoire entre 40px et 120px
      const size = randomBetween(80, 180)
  
      // Position aléatoire (en s'assurant que le cercle reste visible dans le conteneur)
      const left = randomBetween(-20, containerWidth - size / 2)
      const top = randomBetween(-20, containerHeight - size / 2)
  
      // Couleur aléatoire
      const color = colors[randomBetween(0, colors.length - 1)]
  
      // Délai d'animation aléatoire
      const delay = randomBetween(1, 10) / 10
  
      return {
        size,
        left,
        top,
        color,
        delay,
      }
    }
  
    // Fonction pour créer les cercles
    function createRandomCircles() {
      const patternSides = document.querySelectorAll(".pattern-side")
  
      patternSides.forEach((patternSide) => {
        // Supprimer les cercles existants
        patternSide.innerHTML = ""
  
        // Nombre de cercles à créer (entre 8 et 12)
        const circleCount = randomBetween(8, 12)
  
        // Créer les cercles
        for (let i = 0; i < circleCount; i++) {
          const circle = generateRandomCircle(patternSide)
          const circleElement = document.createElement("div")
  
          circleElement.className = "pattern-element"
          circleElement.style.width = `${circle.size}px`
          circleElement.style.height = `${circle.size}px`
          circleElement.style.left = `${circle.left}px`
          circleElement.style.top = `${circle.top}px`
          circleElement.style.backgroundColor = circle.color
          circleElement.style.animationDelay = `${circle.delay}s`
  
          patternSide.appendChild(circleElement)
        }
      })
    }
  
    // Créer les cercles au chargement de la page
    createRandomCircles()
  
    // Recréer les cercles lors du redimensionnement de la fenêtre
    window.addEventListener("resize", () => {
      // Utiliser un délai pour éviter trop d'appels pendant le redimensionnement
      clearTimeout(window.resizeTimer)
      window.resizeTimer = setTimeout(createRandomCircles, 200)
    })
  })
  