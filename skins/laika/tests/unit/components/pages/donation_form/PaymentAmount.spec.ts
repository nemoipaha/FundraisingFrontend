import { shallowMount, mount, createLocalVue } from '@vue/test-utils';
import Vuex, { Store } from 'vuex';
import PaymentAmount from '@/components/pages/donation_form/PaymentAmount.vue';
import { createStore } from '@/store/donation_store';
import { action } from '@/store/util';
import { NS_PAYMENT } from '@/store/namespaces';
import { markEmptyAmountAsInvalid, setAmount } from '@/store/payment/actionTypes';

const localVue = createLocalVue();
localVue.use( Vuex );

describe( 'PaymentAmount', () => {

	it( 'sends new amount to store when amount is selected', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500, 1000, 10000, 29900 ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		wrapper.find( '#amount-29900' ).trigger( 'click' );
		const expectedAction = action( NS_PAYMENT, setAmount );
		const expectedPayload = {
			amountValue: '29900',
			validateAmountUrl: 'https://example.com/amount-check',
		};

		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'clears custom amount when amount is selected', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500, 1000, 10000, 29900 ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		const customAmountInput = wrapper.find( '#amount-custom' );
		customAmountInput.setValue( '5' );
		customAmountInput.trigger( 'blur' );
		wrapper.find( '#amount-29900' ).trigger( 'click' );

		// Can't access (computed) property on generic Vue instance,
		// see https://github.com/vuejs/vue-test-utils/issues/255
		expect( ( wrapper.vm as any ).customAmount ).toBe( '' );
	} );

	it( 'sends cent amount to store when custom amount is entered', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500 ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		const customAmountInput = wrapper.find( '#amount-custom' );
		customAmountInput.setValue( '23' );
		customAmountInput.trigger( 'blur' );
		const expectedAction = action( NS_PAYMENT, setAmount );
		const expectedPayload = {
			amountValue: '2300',
			validateAmountUrl: 'https://example.com/amount-check',
		};

		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'converts custom amounts with decimal point to cent amounts', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500 ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		const customAmountInput = wrapper.find( '#amount-custom' );
		customAmountInput.setValue( '12.34' );
		customAmountInput.trigger( 'blur' );
		const expectedAction = action( NS_PAYMENT, setAmount );
		const expectedPayload = {
			amountValue: '1234',
			validateAmountUrl: 'https://example.com/amount-check',
		};

		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'converts custom amounts with comma to cent amounts', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500 ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		const customAmountInput = wrapper.find( '#amount-custom' );
		customAmountInput.setValue( '23,42' );
		customAmountInput.trigger( 'blur' );
		const expectedAction = action( NS_PAYMENT, setAmount );
		const expectedPayload = {
			amountValue: '2342',
			validateAmountUrl: 'https://example.com/amount-check',
		};

		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'cuts off cent fractions from custom amounts', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500 ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		const customAmountInput = wrapper.find( '#amount-custom' );
		customAmountInput.setValue( '23,429' );
		customAmountInput.trigger( 'blur' );
		const expectedAction = action( NS_PAYMENT, setAmount );
		const expectedPayload = {
			amountValue: '2342',
			validateAmountUrl: 'https://example.com/amount-check',
		};

		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'sends empty string to store when custom amount is invalid', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500 ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		const customAmountInput = wrapper.find( '#amount-custom' );
		customAmountInput.setValue( 'hi mom!' );
		customAmountInput.trigger( 'blur' );
		const expectedAction = action( NS_PAYMENT, setAmount );
		const expectedPayload = {
			amountValue: '',
			validateAmountUrl: 'https://example.com/amount-check',
		};

		expect( store.dispatch ).toBeCalledWith( expectedAction, expectedPayload );
	} );

	it( 'does not trigger an amount check when amount is selected and custom amount is empty', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500, 1000, 10000, 29900 ],
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		wrapper.find( '#amount-29900' ).trigger( 'click' );

		const store = wrapper.vm.$store;
		store.dispatch = jest.fn().mockResolvedValue( null );

		const customAmountInput = wrapper.find( '#amount-custom' );
		customAmountInput.setValue( '' );
		customAmountInput.trigger( 'blur' );
		const forbiddenAction = action( NS_PAYMENT, markEmptyAmountAsInvalid );

		expect( store.dispatch ).not.toBeCalledWith( forbiddenAction );
	} );

	it( 'triggers an amount check in the store when custom value is empty and no amount is selected', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500, 1000, 10000, 29900 ],
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn().mockResolvedValue( null );

		const customAmountInput = wrapper.find( '#amount-custom' );
		customAmountInput.setValue( '' );
		customAmountInput.trigger( 'blur' );
		const expectedAction = action( NS_PAYMENT, markEmptyAmountAsInvalid );
		return expect( store.dispatch ).toBeCalledWith( expectedAction );
	} );

	it( 'clears selected amount when custom amount is entered', () => {
		const wrapper = mount( PaymentAmount, {
			propsData: {
				paymentAmounts: [ 500, 1000, 10000, 29900 ],
				validateAmountUrl: 'https://example.com/amount-check',
			},
			store: createStore(),
			mocks: {
				$t: () => {},
			},
		} );
		const store = wrapper.vm.$store;
		store.dispatch = jest.fn();

		const presetAmount = wrapper.find( '#amount-29900' );
		const customAmountInput = wrapper.find( '#amount-custom' );
		presetAmount.trigger( 'click' );
		customAmountInput.setValue( '1998' );
		customAmountInput.trigger( 'blur' );

		expect( ( wrapper.vm as any ).selectedAmount ).toBe( '' );
	} );

} );
