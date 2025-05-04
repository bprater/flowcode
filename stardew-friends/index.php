<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stardew Valley Heart Tracker</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 1rem;
      background-color: #f4e5b6;
    }
    h1 {
      text-align: center;
      color: #5b3a29;
    }
    .villager {
      background: #fff8dc;
      padding: 1rem;
      border-radius: 10px;
      margin-bottom: 1rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      cursor: pointer;
    }
    .villager-header {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    .villager img {
      width: 48px;
      height: 48px;
      border-radius: 5px;
    }
    .villager-info {
      flex-grow: 1;
    }
    .villager-name {
      font-size: 1.2rem;
      font-weight: bold;
      color: #5b3a29;
    }
    .hearts {
      margin-top: 0.3rem;
    }
    .heart {
      font-size: 1.5rem;
      color: #d94f4f;
      cursor: pointer;
      position: relative;
    }
    .heart-burst {
      position: absolute;
      top: -10px;
      left: -10px;
      width: 30px;
      height: 30px;
      pointer-events: none;
      animation: explode 0.5s ease-out forwards;
    }
    @keyframes explode {
      0% { transform: scale(1); opacity: 1; }
      100% { transform: scale(2); opacity: 0; }
    }
    .container {
      max-width: 500px;
      margin: auto;
    }
    .details {
      display: none;
      margin-top: 1rem;
      padding-top: 0.5rem;
      border-top: 1px solid #a77c4e;
    }
    .gift-section-wrapper {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .gift-section {
      flex: 1;
    }
    .gift-title {
      font-weight: bold;
      color: #8b5c2d;
      margin-bottom: 0.2rem;
    }
    .gift-list {
      margin: 0;
      padding-left: 1.2rem;
    }
    label {
      color: #5b3a29;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Stardew Valley Heart Tracker</h1>
    <div style="text-align: center; margin-bottom: 1rem;">
      <label for="filterOption" style="font-weight: bold;">
        <span style="font-size: 1.2rem;">⚙️</span> Options:
        <input type="checkbox" id="filterOption" /> Show non-full heart only
      </label>
    </div>
    <div id="villager-list"></div>
  </div>

  <script>
    const villagers = 
[
  {
    "name": "Abigail",
    "birthday": "Fall 13",
    "marriage_candidate": true,
    "current_hearts": 7,
    "loved_gifts": ["Amethyst", "Banana Pudding", "Blackberry Cobbler", "Chocolate Cake", "Pufferfish", "Pumpkin", "Spicy Eel"],
    "hated_gifts": ["Clay", "Holly"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/8/88/Abigail.png"
  },
  {
    "name": "Alex",
    "birthday": "Summer 13",
    "marriage_candidate": true,
    "current_hearts": 5,
    "loved_gifts": ["Complete Breakfast", "Salmon Dinner"],
    "hated_gifts": ["Holly", "Quartz"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/0/04/Alex.png"
  },
  {
    "name": "Elliott",
    "birthday": "Fall 5",
    "marriage_candidate": true,
    "current_hearts": 8,
    "loved_gifts": ["Crab Cakes", "Duck Feather", "Lobster", "Pomegranate", "Squid Ink", "Tom Kha Soup"],
    "hated_gifts": ["Amaranth", "Quartz", "Salmonberry", "Sea Cucumber"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/b/bd/Elliott.png"
  },
  {
    "name": "Emily",
    "birthday": "Spring 27",
    "marriage_candidate": true,
    "current_hearts": 9,
    "loved_gifts": ["Amethyst", "Aquamarine", "Cloth", "Emerald", "Jade", "Ruby", "Survival Burger", "Topaz", "Wool"],
    "hated_gifts": ["Fish Taco", "Holly", "Maki Roll", "Salmon Dinner", "Sashimi"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/2/28/Emily.png"
  },
  {
    "name": "Haley",
    "birthday": "Spring 14",
    "marriage_candidate": true,
    "current_hearts": 4,
    "loved_gifts": ["Coconut", "Fruit Salad", "Pink Cake", "Sunflower"],
    "hated_gifts": ["Clay", "Prismatic Shard", "Wild Horseradish"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/1/1b/Haley.png"
  },
  {
    "name": "Harvey",
    "birthday": "Winter 14",
    "marriage_candidate": true,
    "current_hearts": 10,
    "loved_gifts": ["Coffee", "Pickles", "Super Meal", "Truffle Oil", "Wine"],
    "hated_gifts": ["Bread", "Coral", "Nautilus Shell", "Rainbow Shell", "Salmonberry", "Spice Berry", "Sugar"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/9/95/Harvey.png"
  },
  {
    "name": "Leah",
    "birthday": "Winter 23",
    "marriage_candidate": true,
    "current_hearts": 6,
    "loved_gifts": ["Goat Cheese", "Poppyseed Muffin", "Salad", "Stir Fry", "Truffle", "Vegetable Medley", "Wine"],
    "hated_gifts": ["Bread", "Hashbrowns", "Pancakes", "Pizza", "Void Egg"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/e/e6/Leah.png"
  },
  {
    "name": "Maru",
    "birthday": "Summer 10",
    "marriage_candidate": true,
    "current_hearts": 7,
    "loved_gifts": ["Battery Pack", "Cauliflower", "Cheese Cauliflower", "Diamond", "Gold Bar", "Iridium Bar", "Miner's Treat", "Pepper Poppers", "Rhubarb Pie", "Strawberry"],
    "hated_gifts": ["Holly", "Honey", "Pickles", "Snow Yam", "Truffle"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/f/f8/Maru.png"
  },
  {
    "name": "Penny",
    "birthday": "Fall 2",
    "marriage_candidate": true,
    "current_hearts": 8,
    "loved_gifts": ["Diamond", "Emerald", "Melon", "Poppy", "Poppyseed Muffin", "Red Plate", "Roots Platter", "Sandfish", "Tom Kha Soup"],
    "hated_gifts": ["Beer", "Grape", "Holly", "Hops", "Mead", "Pale Ale", "Piña Colada", "Rabbit's Foot", "Wine"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/a/a9/Penny.png"
  },
  {
    "name": "Sam",
    "birthday": "Summer 17",
    "marriage_candidate": true,
    "current_hearts": 5,
    "loved_gifts": ["Cactus Fruit", "Maple Bar", "Pizza", "Tigerseye"],
    "hated_gifts": ["Coal", "Copper Bar", "Duck Mayonnaise", "Gold Bar", "Iridium Bar", "Iron Bar", "Mayonnaise", "Pickles", "Refined Quartz"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/9/94/Sam.png"
  },
  {
    "name": "Sebastian",
    "birthday": "Winter 10",
    "marriage_candidate": true,
    "current_hearts": 9,
    "loved_gifts": ["Frozen Tear", "Obsidian", "Pumpkin Soup", "Sashimi", "Void Egg"],
    "hated_gifts": ["Clay", "Complete Breakfast", "Farmer's Lunch", "Omelet"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/a/a8/Sebastian.png"
  },
  {
    "name": "Shane",
    "birthday": "Spring 20",
    "marriage_candidate": true,
    "current_hearts": 7,
    "loved_gifts": ["Beer", "Hot Pepper", "Pepper Poppers", "Pizza"],
    "hated_gifts": ["Pickles", "Quartz"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/8/8b/Shane.png"
  },
  {
    "name": "Caroline",
    "birthday": "Winter 7",
    "marriage_candidate": false,
    "current_hearts": 6,
    "loved_gifts": ["Fish Taco", "Green Tea", "Summer Spangle", "Tropical Curry"],
    "hated_gifts": ["Amaranth", "Duck Mayonnaise", "Mayonnaise", "Nopales", "Quartz", "Salmonberry"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/8/87/Caroline.png"
  },
  {
    "name": "Clint",
    "birthday": "Winter 26",
    "marriage_candidate": false,
    "current_hearts": 3,
    "loved_gifts": ["Amethyst", "Aquamarine", "Artichoke Dip", "Emerald", "Fiddlehead Risotto", "Gold Bar", "Iridium Bar", "Jade", "Omni Geode", "Ruby", "Topaz"],
    "hated_gifts": ["Holly", "Quartz"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/3/31/Clint.png"
  },
  {
    "name": "Demetrius",
    "birthday": "Summer 19",
    "marriage_candidate": false,
    "current_hearts": 5,
    "loved_gifts": ["Bean Hotpot", "Ice Cream", "Rice Pudding", "Strawberry"],
    "hated_gifts": ["Holly", "Quartz"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/f/f9/Demetrius.png"
  },
  {
    "name": "Evelyn",
    "birthday": "Winter 20",
    "marriage_candidate": false,
    "current_hearts": 8,
    "loved_gifts": ["Beet", "Chocolate Cake", "Diamond", "Fairy Rose", "Stuffing", "Tulip"],
    "hated_gifts": ["Clay", "Clam", "Coral", "Fried Eel", "Garlic", "Holly", "Maki Roll", "Salmonberry", "Sashimi", "Spice Berry", "Spicy Eel", "Trout Soup"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/8/8e/Evelyn.png"
  },
  {
    "name": "George",
    "birthday": "Fall 24",
    "marriage_candidate": false,
    "current_hearts": 4,
    "loved_gifts": ["Fried Mushroom", "Leek"],
    "hated_gifts": ["Clay", "Dandelion", "Holly", "Quartz"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/7/78/George.png"
  },
  {
    "name": "Gus",
    "birthday": "Summer 8",
    "marriage_candidate": false,
    "current_hearts": 7,
    "loved_gifts": ["Diamond", "Escargot", "Fish Taco", "Orange", "Tropical Curry"],
    "hated_gifts": ["Coleslaw", "Holly", "Quartz"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/5/52/Gus.png"
  },
  {
    "name": "Jas",
    "birthday": "Summer 4",
    "marriage_candidate": false,
    "current_hearts": 5,
    "loved_gifts": ["Fairy Rose", "Pink Cake", "Plum Pudding"],
    "hated_gifts": ["Clay", "Triple Shot Espresso", "Wild Horseradish", "Coffee"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/5/55/Jas.png"
  },
  {
    "name": "Jodi",
    "birthday": "Fall 11",
    "marriage_candidate": false,
    "current_hearts": 6,
    "loved_gifts": ["Chocolate Cake", "Crispy Bass", "Diamond", "Eggplant Parmesan", "Fried Eel", "Pancakes", "Rhubarb Pie", "Vegetable Medley"],
    "hated_gifts": ["Daffodil", "Dandelion", "Garlic", "Holly", "Spice Berry"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/4/41/Jodi.png"
  },
  {
    "name": "Kent",
    "birthday": "Spring 4",
    "marriage_candidate": false,
    "current_hearts": 3,
    "loved_gifts": ["Fiddlehead Risotto", "Roasted Hazelnuts"],
    "hated_gifts": ["Algae Soup", "Holly", "Sashimi", "Snow Yam", "Tortilla"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/9/99/Kent.png"
  },
  {
    "name": "Lewis",
    "birthday": "Spring 7",
    "marriage_candidate": false,
    "current_hearts": 7,
    "loved_gifts": ["Autumn's Bounty", "Glazed Yams", "Green Tea", "Hot Pepper", "Vegetable Medley"],
    "hated_gifts": ["Holly", "Quartz"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/2/2b/Lewis.png"
  },
  {
    "name": "Linus",
    "birthday": "Winter 3",
    "marriage_candidate": false,
    "current_hearts": 8,
    "loved_gifts": ["Blueberry Tart", "Cactus Fruit", "Coconut", "Dish o' The Sea", "Yam"],
    "hated_gifts": ["Bug Meat", "Green Algae", "Joja Cola", "Rotten Plant", "Sap", "Seaweed", "Trash", "White Algae"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/3/31/Linus.png"
  },
  {
    "name": "Marnie",
    "birthday": "Fall 18",
    "marriage_candidate": false,
    "current_hearts": 6,
    "loved_gifts": ["Diamond", "Farmer's Lunch", "Pink Cake", "Pumpkin Pie"],
    "hated_gifts": ["Clay", "Holly", "Seaweed"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/5/52/Marnie.png"
  },
  {
    "name": "Pam",
    "birthday": "Spring 18",
    "marriage_candidate": false,
    "current_hearts": 4,
    "loved_gifts": ["Beer", "Cactus Fruit", "Glazed Yams", "Mead", "Pale Ale", "Parsnip", "Parsnip Soup", "Piña Colada"],
    "hated_gifts": ["Holly", "Octopus", "Quartz", "Squid"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/d/da/Pam.png"
  },
  {
    "name": "Pierre",
    "birthday": "Spring 26",
    "marriage_candidate": false,
    "current_hearts": 5,
    "loved_gifts": ["Fried Calamari"],
    "hated_gifts": ["Corn", "Garlic", "Parsnip Soup", "Tortilla", "Blue Jazz", "Common Mushroom", "Holly", "Poppy", "Salmonberry", "Spice Berry", "Sweet Pea"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/7/7e/Pierre.png"
  },
  {
    "name": "Robin",
    "birthday": "Fall 21",
    "marriage_candidate": false,
    "current_hearts": 7,
    "loved_gifts": ["Goat Cheese", "Peach", "Spaghetti"],
    "hated_gifts": ["Holly", "Wild Horseradish"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/1/1b/Robin.png"
  },
  {
    "name": "Sandy",
    "birthday": "Fall 15",
    "marriage_candidate": false,
    "current_hearts": 6,
    "loved_gifts": ["Crocus", "Daffodil", "Mango Sticky Rice", "Sweet Pea"],
    "hated_gifts": ["Holly", "Quartz"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/4/4e/Sandy.png"
  },
  {
    "name": "Vincent",
    "birthday": "Spring 10",
    "marriage_candidate": false,
    "current_hearts": 5,
    "loved_gifts": ["Cranberry Candy", "Ginger Ale", "Grape", "Pink Cake", "Snail"],
    "hated_gifts": ["Clay", "Triple Shot Espresso", "Wild Horseradish", "Coffee"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/f/f1/Vincent.png"
  },
  {
    "name": "Willy",
    "birthday": "Summer 24",
    "marriage_candidate": false,
    "current_hearts": 6,
    "loved_gifts": ["Catfish", "Diamond", "Iridium Bar", "Mead", "Octopus", "Pumpkin", "Sea Cucumber", "Sturgeon"],
    "hated_gifts": ["Bread", "Cookies", "Hay", "Omelet", "Pancakes", "Strange Bun", "Tortilla", "Void Mayonnaise", "Life Elixir"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/8/82/Willy.png"
  },
  {
    "name": "Wizard",
    "birthday": "Winter 17",
    "marriage_candidate": false,
    "current_hearts": 7,
    "loved_gifts": ["Book Of Stars", "Purple Mushroom", "Solar Essence", "Super Cucumber", "Void Essence"],
    "hated_gifts": ["Clay", "Holly", "Weeds", "Sap", "Slime"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/c/c7/Wizard.png"
  },
  {
    "name": "Krobus",
    "birthday": "Winter 1",
    "marriage_candidate": false,
    "current_hearts": 8,
    "loved_gifts": ["Diamond", "Iridium Bar", "Pumpkin", "Void Egg", "Void Mayonnaise", "Wild Horseradish"],
    "hated_gifts": ["Life Elixir", "Garlic Oil", "Cranberry Sauce", "Glazed Yams", "Hashbrowns", "Pizza", "Roasted Hazelnuts", "Stuffing"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/7/71/Krobus.png"
  },
  {
    "name": "Dwarf",
    "birthday": "Summer 22",
    "marriage_candidate": false,
    "current_hearts": 4,
    "loved_gifts": ["Amethyst", "Aquamarine", "Emerald", "Jade", "Lemon Stone", "Omni Geode", "Ruby", "Topaz"],
    "hated_gifts": ["Clay", "Holly", "Weeds", "Sap", "Wild Horseradish", "All Universal Hates"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/e/ed/Dwarf.png"
  },
  {
    "name": "Leo",
    "birthday": "Summer 26",
    "marriage_candidate": false,
    "current_hearts": 5,
    "loved_gifts": ["Duck Feather", "Mango", "Ostrich Egg", "Poi"],
    "hated_gifts": ["Beer", "Coffee", "Holly", "Hops", "Mead", "Morel", "Oil", "Pale Ale", "Pickles", "Pina Colada", "Triple Shot Espresso", "Unmilled Rice", "Wine"],
    "image": "https://stardewvalleywiki.com/mediawiki/images/1/1d/Leo.png"
  }
];

    const heartIcon = (filled) => filled ? '❤️' : '🤍';

    const renderVillagers = () => {
      const container = document.getElementById('villager-list');
      container.innerHTML = '';

      villagers.filter(v => {
        const showOnlyNonFull = document.getElementById('filterOption').checked;
        return !showOnlyNonFull || v.current_hearts < 10;
      }).forEach((v, index) => {
        const villagerDiv = document.createElement('div');
        villagerDiv.className = 'villager';

        const header = document.createElement('div');
        header.className = 'villager-header';

        const portrait = document.createElement('img');
        portrait.src = v.image;
        portrait.alt = `${v.name}'s portrait`;

        const info = document.createElement('div');
        info.className = 'villager-info';

        const name = document.createElement('div');
        name.className = 'villager-name';
        name.textContent = v.name;

        const hearts = document.createElement('div');
        hearts.className = 'hearts';

        for (let i = 0; i < 10; i++) {
          const heart = document.createElement('span');
          heart.className = 'heart';
          heart.textContent = heartIcon(i < v.current_hearts);
          heart.dataset.index = i;
          heart.addEventListener('click', (event) => {
            event.stopPropagation();
            v.current_hearts = i + 1;

            const currentHearts = JSON.parse(localStorage.getItem('villagerHearts') || '{}');
            currentHearts[v.name] = v.current_hearts;
            localStorage.setItem('villagerHearts', JSON.stringify(currentHearts));

            const burst = document.createElement('div');
            burst.className = 'heart-burst';
            burst.textContent = '💖';
            burst.style.position = 'absolute';
            burst.style.left = '0';
            burst.style.top = '0';
            burst.style.fontSize = '1.2rem';
            burst.style.color = '#e25555';
            heart.appendChild(burst);

            renderVillagers();
            setTimeout(() => {
              heart.removeChild(burst);
            }, 500);
          });
          hearts.appendChild(heart);
        }

        info.appendChild(name);
        info.appendChild(hearts);
        header.appendChild(portrait);
        header.appendChild(info);
        villagerDiv.appendChild(header);

        const details = document.createElement('div');
        details.className = 'details';

        const loved = document.createElement('div');
        loved.className = 'gift-section';
        loved.innerHTML = `<div class="gift-title">Loved Gifts:</div><ul class="gift-list">${[...v.loved_gifts].sort().map(g => `<li>${g}</li>`).join('')}</ul>`;

        const hated = document.createElement('div');
        hated.className = 'gift-section';
        hated.innerHTML = `<div class="gift-title">Hated Gifts:</div><ul class="gift-list">${[...v.hated_gifts].sort().map(g => `<li>${g}</li>`).join('')}</ul>`;

        const wrapper = document.createElement('div');
        wrapper.className = 'gift-section-wrapper';
        wrapper.appendChild(loved);
        wrapper.appendChild(hated);
        details.appendChild(wrapper);
        villagerDiv.appendChild(details);

        villagerDiv.addEventListener('click', () => {
          details.style.display = details.style.display === 'block' ? 'none' : 'block';
        });

        container.appendChild(villagerDiv);
      });
    };

    renderVillagers();
  </script>
</body>
</html>
