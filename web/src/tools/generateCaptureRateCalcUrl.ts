import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';

interface CaptureRateCalcUrlState {
    pokemonAttacking: Pick<ApiRecord.Pokemon.Pokemon, 'slug'> | null
    pokemonDefending: Pick<ApiRecord.Pokemon.Pokemon, 'slug'> | null
    method: Pick<ApiRecord.Pokemon.EncounterMethod, 'slug'> | null
    levelAttacking: number
    levelDefending: number
    genderAttacking: Pick<ApiRecord.Pokemon.Gender, 'slug'> | null
    genderDefending: Pick<ApiRecord.Pokemon.Gender, 'slug'> | null
    timeOfDay: Pick<ApiRecord.TimeOfDay, 'slug'> | null
    inDarkGrass: boolean
    pokedexCount: number
    capturePower: number
    hp: number
}

export default function generateCaptureRateCalcUrl(state: Partial<CaptureRateCalcUrlState>, version: ApiRecord.Version) {
    const urlParams = new URLSearchParams();

    if (state.pokemonAttacking) {
        urlParams.set('pokemonAttacking', state.pokemonAttacking.slug);
    }
    if (state.pokemonDefending) {
        urlParams.set('pokemonDefending', state.pokemonDefending.slug);
    }
    if (state.method) {
        urlParams.set('method', state.method.slug);
    }
    if (state.levelAttacking !== undefined) {
        urlParams.set('levelAttacking', String(state.levelAttacking));
    }
    if (state.levelDefending !== undefined) {
        urlParams.set('levelDefending', String(state.levelDefending));
    }
    if (state.genderAttacking) {
        urlParams.set('genderAttacking', state.genderAttacking.slug);
    }
    if (state.genderDefending) {
        urlParams.set('genderDefending', state.genderDefending.slug);
    }
    if (state.timeOfDay) {
        urlParams.set('timeOfDay', state.timeOfDay.slug);
    }
    if (state.inDarkGrass !== undefined) {
        urlParams.set('inDarkGrass', state.inDarkGrass ? '1' : '0');
    }
    if (state.pokedexCount !== undefined) {
        urlParams.set('pokedexCount', String(state.pokedexCount));
    }
    if (state.capturePower !== undefined) {
        urlParams.set('capturePower', String(state.capturePower));
    }
    if (state.hp !== undefined) {
        urlParams.set('hp', String(state.hp));
    }

    return generatePath(Routes.TOOLS_CAPTURE_RATE, {version: version.slug}) + '?' + urlParams.toString();
}
