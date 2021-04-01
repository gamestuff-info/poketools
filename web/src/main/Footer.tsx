import {Nav, Navbar as BsNavbar} from 'react-bootstrap';
import {NavLink} from 'react-router-dom';

export default function Footer(props: {}) {
    return (
        <BsNavbar expand variant="dark" bg="dark">
            <Nav className="mr-auto">
                <Nav.Link as={NavLink} to="/about/credits">Credits</Nav.Link>
                <Nav.Link href="https://github.com/gamestuff-info/poketools">Contribute</Nav.Link>
                <Nav.Link href="https://gamestuff.info/">gamestuff.info</Nav.Link>
            </Nav>
        </BsNavbar>
    );
}
