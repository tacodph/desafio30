export type Workout = { type: string; minutes: number | '' };

export type DayDetails = {
    workouts: Workout[];
    water_ml: number;
    sleep_hours: number | '';
    food: { no_alcohol: boolean; no_fast_food: boolean; no_sweet: boolean; luxury: boolean };
    skill: string;
    manual: { movement: boolean; water: boolean; sleep: boolean; strength: boolean; skill: boolean };
};

export type DayGoals = { movement_minutes: number; water_ml: number; sleep_hours: number; enabled?: string[] };

export type Achieved = { movement: boolean; water: boolean; sleep: boolean; strength: boolean; skill: boolean };

export const workoutMinutes = (details: DayDetails) => details.workouts.reduce((sum, item) => sum + (Number(item.minutes) || 0), 0);

/** Mesma regra do servidor (App\Support\DayLog::achieved): meta batida OU contado manualmente. */
export const achieved = (details: DayDetails, goals: DayGoals): Achieved => ({
    movement: workoutMinutes(details) >= goals.movement_minutes || details.manual.movement,
    water: details.water_ml >= goals.water_ml || details.manual.water,
    sleep: Number(details.sleep_hours || 0) >= goals.sleep_hours || details.manual.sleep,
    strength: (details.food.no_alcohol && details.food.no_fast_food && details.food.no_sweet) || details.food.luxury || details.manual.strength,
    skill: details.skill !== '' || details.manual.skill,
});

export const dayPoints = (checkedIn: boolean, details: DayDetails, goals: DayGoals, scores: Record<string, number> = {}) => {
    const done = achieved(details, goals);
    const p = (key: string, fallback: number) => scores[key] ?? fallback;

    return (
        (checkedIn ? p('checkin', 1) : 0) +
        (done.movement && (!goals.enabled || goals.enabled.includes('movement')) ? p('movement', 3) : 0) +
        (done.water && (!goals.enabled || goals.enabled.includes('water')) ? p('water', 1) : 0) +
        (done.sleep && (!goals.enabled || goals.enabled.includes('sleep')) ? p('sleep', 1) : 0) +
        (done.strength && (!goals.enabled || goals.enabled.includes('strength')) ? p('strength', 2) : 0) +
        (done.skill && (!goals.enabled || goals.enabled.includes('skill')) ? p('skill', 2) : 0)
    );
};
