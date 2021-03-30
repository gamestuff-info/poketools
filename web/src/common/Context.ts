import {createContext} from 'react';
import {Flash} from './components/Flashes';

export interface AppContextProps {
    currentVersion?: ApiRecord.Version
    setFlashes?: (flashes: Array<Flash>) => void
}

const AppContext = createContext({} as AppContextProps);

export default AppContext;
